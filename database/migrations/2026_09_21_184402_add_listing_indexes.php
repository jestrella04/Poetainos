<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index the columns the listings sort and filter by.
     */
    public function up(): void
    {
        Schema::table('writings', function (Blueprint $table): void {
            $table->index('created_at');
            $table->index('views');
            $table->index('aura');
            $table->index('home_posted_at');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('aura');
            $table->index('karma');
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->index(['writing_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('writings', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['views']);
            $table->dropIndex(['aura']);
            $table->dropIndex(['home_posted_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['aura']);
            $table->dropIndex(['karma']);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex(['writing_id', 'created_at']);
        });
    }
};
