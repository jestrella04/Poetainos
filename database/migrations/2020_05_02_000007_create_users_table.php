<?php

use App\Models\Role;
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
            $table->unsignedBigInteger('role_id')->nullable();
			$table->string('name')->nullable();
            $table->string('email')->unique();
			$table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('profile_views')->default(0);
            $table->double('aura')->unsigned()->default(0);
            $table->json('extra_info')->nullable();
			$table->timestamps();
            $table->timestamp('aura_updated_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('password_updated_at')->nullable();

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
