<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
			$table->unsignedBigInteger('blocked_user_id');
            $table->primary(['user_id','blocked_user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
			$table->foreign('blocked_user_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blocked_users');
    }
}
