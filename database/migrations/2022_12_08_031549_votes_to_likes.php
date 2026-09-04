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
            $table->string('likeable_type')->after('id');
            $table->unsignedInteger('likeable_id')->after('likeable_type');
            $table->index(['likeable_type', 'likeable_id']);
        });

        DB::table('likes')->update(['likeable_type' => 'App\Models\Writing']);
        DB::statement('UPDATE `likes` SET `likeable_id` = `writing_id`');

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER TABLE ... DROP FOREIGN KEY / DROP INDEX;
            // the schema builder rebuilds the table instead.
            Schema::table('likes', function (Blueprint $table) {
                $table->dropUnique('votes_writing_id_user_id_unique');
                $table->dropForeign(['writing_id']);
                $table->dropColumn('writing_id');
            });
        } else {
            DB::statement('ALTER TABLE `likes` DROP FOREIGN KEY `votes_writing_id_foreign`');
            DB::statement('ALTER TABLE `likes` DROP INDEX `votes_writing_id_user_id_unique`');
            DB::statement('ALTER TABLE `likes` DROP COLUMN `writing_id`');
        }

        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['likeable_type', 'likeable_id', 'user_id']);
        });
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
