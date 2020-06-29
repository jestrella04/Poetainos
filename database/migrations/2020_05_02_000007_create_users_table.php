<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
            $table->bigIncrements('id');
            $table->string('username')->unique();
            $table->unsignedBigInteger('role_id');
			$table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
			$table->string('password');
            $table->timestamp('password_updated_at')->nullable();
            $table->rememberToken();
			$table->unsignedBigInteger('profile_views')->default(0);
			$table->boolean('is_enabled')->default(1);
			$table->timestamps();
			$table->double('aura')->unsigned()->default(0);
            $table->timestamp('aura_updated_at')->nullable();
            $table->json('extra_info')->nullable();

            $table->foreign('role_id')->references('id')->on('roles');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}
}
