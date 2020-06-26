<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagWritingTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tag_writing', function(Blueprint $table)
		{
			$table->unsignedBigInteger('writing_id');
			$table->unsignedBigInteger('tag_id');
            $table->primary(['writing_id','tag_id']);

            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('CASCADE');
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
		Schema::dropIfExists('tag_writing');
	}
}
