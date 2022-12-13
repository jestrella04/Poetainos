<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('votes', 'likes');
        Schema::table('likes', function (Blueprint $table) {
            $table->string("likeable_type")->after('id');
            $table->unsignedInteger("likeable_id")->after('likeable_type');
            $table->index(["likeable_type", "likeable_id"]);

            $table->unique(['likeable_type', 'likeable_id', 'user_id']);
        });

        DB::table('likes')->update(['likeable_type' => 'App\Models\Writing']);
        DB::statement('UPDATE `likes` SET `likeable_id` = `writing_id`');
        DB::statement('ALTER TABLE `likes` DROP FOREIGN KEY `votes_writing_id_foreign`');
        DB::statement('ALTER TABLE `likes` DROP INDEX `votes_writing_id_user_id_unique`');
        DB::statement('ALTER TABLE `likes` DROP COLUMN `writing_id`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
};
