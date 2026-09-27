<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Checks that every value the users.extra_info and writings.extra_info JSON
 * columns held was copied to its new column or table, following the same
 * rules as the 2026_09_26_174300 migration that made the copy. A difference
 * on a record changed after the copy is the user's own edit, not a lost value.
 */
class ExtraInfoCopyVerifier
{
    private const PROFILE_FIELDS = ['avatar', 'bio', 'website', 'location', 'occupation', 'interests'];

    private const SOCIAL_NETWORKS = ['twitter', 'threads', 'instagram', 'facebook', 'youtube', 'goodreads'];

    /**
     * Whether there is still anything to verify.
     */
    public function hasLegacyColumns(): bool
    {
        return Schema::hasColumn('users', 'extra_info') || Schema::hasColumn('writings', 'extra_info');
    }

    /**
     * When the copy ran: the backfilled profiles were all created then, and
     * any profile a user created afterwards is newer.
     */
    public function copiedAt(): ?Carbon
    {
        $earliest = DB::table('user_profiles')->min('created_at');

        return $earliest === null ? null : Carbon::parse($earliest);
    }

    /**
     * Every value that differs between the JSON and its new home.
     *
     * @return Collection<int, array{table: string, id: int, field: string, legacy: mixed, current: mixed, changedSinceCopy: bool}>
     */
    public function differences(?Carbon $copiedAt): Collection
    {
        return $this->userDifferences($copiedAt)->merge($this->writingDifferences($copiedAt))->values();
    }

    /**
     * @return Collection<int, array{table: string, id: int, field: string, legacy: mixed, current: mixed, changedSinceCopy: bool}>
     */
    private function userDifferences(?Carbon $copiedAt): Collection
    {
        $differences = collect();

        if (Schema::hasColumn('users', 'extra_info') === false) {
            return $differences;
        }

        DB::table('users')
            ->select('id', 'extra_info', 'updated_at', 'terms_accepted_at', 'privacy_accepted_at', 'wants_email_notifications')
            ->orderBy('id')
            ->chunkById(500, function (Collection $users) use ($differences, $copiedAt): void {
                $profiles = DB::table('user_profiles')->whereIn('user_id', $users->pluck('id'))->get()->keyBy('user_id');
                $providers = DB::table('social_accounts')->whereIn('user_id', $users->pluck('id'))->get()->groupBy('user_id');

                foreach ($users as $user) {
                    $info = json_decode((string) $user->extra_info, true);

                    if (is_array($info) === false) {
                        continue;
                    }

                    $profile = $profiles->get($user->id);
                    $isProfileEdited = $profile !== null && $this->isChangedSince($profile->updated_at, $copiedAt);
                    $isAccountEdited = $this->isChangedSince($user->updated_at, $copiedAt);

                    $expectedProfile = [];

                    foreach (self::PROFILE_FIELDS as $field) {
                        $expectedProfile[$field] = $this->textOrNull($info[$field] ?? null);
                    }

                    foreach (self::SOCIAL_NETWORKS as $network) {
                        $expectedProfile[$network] = $this->textOrNull($info['social'][$network] ?? null);
                    }

                    foreach ($expectedProfile as $field => $legacy) {
                        $current = $profile?->{$field};

                        if ($legacy !== $current) {
                            $differences->push($this->difference('users', $user->id, "profile.{$field}", $legacy, $current, $isProfileEdited));
                        }
                    }

                    $expectedAccount = [
                        'terms_accepted' => $this->isChecked($info['agreement']['terms_of_use'] ?? null),
                        'privacy_accepted' => $this->isChecked($info['agreement']['privacy_policy'] ?? null),
                        'wants_email_notifications' => $this->wantsEmail($info['notifications']['email'] ?? null),
                    ];
                    $currentAccount = [
                        'terms_accepted' => $user->terms_accepted_at !== null,
                        'privacy_accepted' => $user->privacy_accepted_at !== null,
                        'wants_email_notifications' => (bool) $user->wants_email_notifications,
                    ];

                    foreach ($expectedAccount as $field => $legacy) {
                        if ($legacy !== $currentAccount[$field]) {
                            $differences->push($this->difference('users', $user->id, $field, $legacy, $currentAccount[$field], $isAccountEdited));
                        }
                    }

                    // Providers can only be added since the copy, so every legacy one must still be there
                    $linkedProviders = $providers->get($user->id, collect())->pluck('provider');

                    foreach ((array) ($info['linked_providers'] ?? []) as $provider) {
                        if ($linkedProviders->contains((string) $provider) === false) {
                            $differences->push($this->difference('users', $user->id, 'linked_provider', $provider, null, false));
                        }
                    }
                }
            });

        return $differences;
    }

    /**
     * @return Collection<int, array{table: string, id: int, field: string, legacy: mixed, current: mixed, changedSinceCopy: bool}>
     */
    private function writingDifferences(?Carbon $copiedAt): Collection
    {
        $differences = collect();

        if (Schema::hasColumn('writings', 'extra_info') === false) {
            return $differences;
        }

        DB::table('writings')
            ->select('id', 'extra_info', 'updated_at', 'cover', 'link')
            ->orderBy('id')
            ->chunkById(500, function (Collection $writings) use ($differences, $copiedAt): void {
                foreach ($writings as $writing) {
                    $info = json_decode((string) $writing->extra_info, true);

                    if (is_array($info) === false) {
                        continue;
                    }

                    $isEdited = $this->isChangedSince($writing->updated_at, $copiedAt);

                    foreach (['cover', 'link'] as $field) {
                        $legacy = $this->textOrNull($info[$field] ?? null);

                        if ($legacy !== $writing->{$field}) {
                            $differences->push($this->difference('writings', $writing->id, $field, $legacy, $writing->{$field}, $isEdited));
                        }
                    }
                }
            });

        return $differences;
    }

    /**
     * @return array{table: string, id: int, field: string, legacy: mixed, current: mixed, changedSinceCopy: bool}
     */
    private function difference(string $table, int $id, string $field, mixed $legacy, mixed $current, bool $changedSinceCopy): array
    {
        return [
            'table' => $table,
            'id' => $id,
            'field' => $field,
            'legacy' => $legacy,
            'current' => $current,
            'changedSinceCopy' => $changedSinceCopy,
        ];
    }

    private function isChangedSince(?string $updatedAt, ?Carbon $copiedAt): bool
    {
        return $copiedAt !== null && $updatedAt !== null && Carbon::parse($updatedAt)->greaterThan($copiedAt);
    }

    private function textOrNull(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function isChecked(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }

    private function wantsEmail(mixed $setting): bool
    {
        return $setting === null || $setting === '' || $this->isChecked($setting);
    }
}
