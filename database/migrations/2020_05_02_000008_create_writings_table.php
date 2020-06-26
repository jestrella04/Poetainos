<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWritingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('writings', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('user_id');
			$table->string('title');
			$table->string('slug')->unique();
			$table->text('text');
			$table->unsignedBigInteger('views')->default(0);
			$table->timestamps();
			$table->double('aura')->unsigned()->default(0);
            $table->timestamp('aura_updated_at')->nullable();
            $table->timestamp('home_posted_at')->nullable();
            $table->json('extra_info')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('writings');
	}
}
