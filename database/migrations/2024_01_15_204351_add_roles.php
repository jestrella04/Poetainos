<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->insert(['name' => 'moderator', 'description' => 'Moderator role']);
        $id = DB::table('roles')->insertGetId(['name' => 'user', 'description' => 'Standard access role']);

        DB::statement("UPDATE `users` SET `role_id` = $id WHERE `role_id` IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
