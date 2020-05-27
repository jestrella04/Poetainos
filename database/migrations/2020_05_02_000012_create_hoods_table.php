<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoodsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hoods', function(Blueprint $table)
		{
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('fellow_user_id');
            $table->primary(['user_id','fellow_user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
			$table->foreign('fellow_user_id')->references('id')->on('users')->onDelete('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('hoods');
	}
}
