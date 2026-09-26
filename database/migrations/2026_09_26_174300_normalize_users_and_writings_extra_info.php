<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves what users.extra_info and writings.extra_info held into real
 * columns and tables, copying every existing value. The JSON columns stay
 * until a later migration drops them, once this copy has been verified.
 */
return new class extends Migration
{
    private const PROFILE_FIELDS = ['avatar', 'bio', 'website', 'location', 'occupation', 'interests'];

    private const SOCIAL_NETWORKS = ['twitter', 'threads', 'instagram', 'facebook', 'youtube', 'goodreads'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('avatar')->nullable();
            $table->string('bio', 300)->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('interests')->nullable();
            $table->string('twitter')->nullable();
            $table->string('threads')->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('facebook')->nullable();
            $table->string('youtube', 100)->nullable();
            $table->string('goodreads')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider', 30);
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('password_updated_at');
            $table->timestamp('privacy_accepted_at')->nullable()->after('terms_accepted_at');
            $table->boolean('wants_email_notifications')->default(true)->after('privacy_accepted_at');
        });

        Schema::table('writings', function (Blueprint $table) {
            $table->string('cover')->nullable()->after('text');
            $table->string('link')->nullable()->after('cover');
        });

        $this->copyUsers();
        $this->copyWritings();
    }

    /**
     * Reverse the migrations. The JSON columns still hold the original values.
     */
    public function down(): void
    {
        Schema::table('writings', function (Blueprint $table) {
            $table->dropColumn(['cover', 'link']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'privacy_accepted_at', 'wants_email_notifications']);
        });

        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('user_profiles');
    }

    private function copyUsers(): void
    {
        DB::table('users')->select('id', 'extra_info', 'created_at')->orderBy('id')->chunkById(500, function ($users): void {
            foreach ($users as $user) {
                $info = json_decode((string) $user->extra_info, true);

                if (is_array($info) === false) {
                    continue;
                }

                $this->copyProfile($user->id, $info);
                $this->copyAccountSettings($user->id, $info, $user->created_at);

                foreach ((array) ($info['linked_providers'] ?? []) as $provider) {
                    DB::table('social_accounts')->insertOrIgnore([
                        'user_id' => $user->id,
                        'provider' => (string) $provider,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->created_at,
                    ]);
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $info
     */
    private function copyProfile(int $userId, array $info): void
    {
        $profile = [];

        foreach (self::PROFILE_FIELDS as $field) {
            $profile[$field] = $this->textOrNull($info[$field] ?? null);
        }

        foreach (self::SOCIAL_NETWORKS as $network) {
            $profile[$network] = $this->textOrNull($info['social'][$network] ?? null);
        }

        if (array_filter($profile, fn (?string $value): bool => $value !== null) === []) {
            return;
        }

        DB::table('user_profiles')->insert([
            ...$profile,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Agreements were stored as checkbox values ("on"); their acceptance date
     * wasn't kept, so the registration date stands in for it. Email
     * notifications were on unless explicitly turned off.
     *
     * @param  array<string, mixed>  $info
     */
    private function copyAccountSettings(int $userId, array $info, ?string $createdAt): void
    {
        $acceptedAt = $createdAt ?? now();
        $emailSetting = $info['notifications']['email'] ?? null;

        DB::table('users')->where('id', $userId)->update([
            'terms_accepted_at' => $this->isChecked($info['agreement']['terms_of_use'] ?? null) ? $acceptedAt : null,
            'privacy_accepted_at' => $this->isChecked($info['agreement']['privacy_policy'] ?? null) ? $acceptedAt : null,
            'wants_email_notifications' => $emailSetting === null || $emailSetting === '' || $this->isChecked($emailSetting),
        ]);
    }

    private function copyWritings(): void
    {
        DB::table('writings')->select('id', 'extra_info')->orderBy('id')->chunkById(500, function ($writings): void {
            foreach ($writings as $writing) {
                $info = json_decode((string) $writing->extra_info, true);

                if (is_array($info) === false) {
                    continue;
                }

                DB::table('writings')->where('id', $writing->id)->update([
                    'cover' => $this->textOrNull($info['cover'] ?? null),
                    'link' => $this->textOrNull($info['link'] ?? null),
                ]);
            }
        });
    }

    private function textOrNull(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function isChecked(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
};
