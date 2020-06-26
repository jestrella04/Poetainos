<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShelvesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shelves', function(Blueprint $table)
		{
			$table->unsignedBigInteger('user_id');
			$table->unsignedBigInteger('writing_id');
            $table->primary(['user_id','writing_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
			$table->foreign('writing_id')->references('id')->on('writings')->onDelete('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('shelves');
	}
}
