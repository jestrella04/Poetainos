<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVotesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('votes', function(Blueprint $table)
		{
            $table->bigIncrements('id');
			$table->unsignedBigInteger('writing_id');
			$table->unsignedBigInteger('user_id');
			$table->unsignedTinyInteger('vote');
            $table->timestamp('created_at');

            $table->unique(['writing_id','user_id']);
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
		Schema::dropIfExists('votes');
	}
}
