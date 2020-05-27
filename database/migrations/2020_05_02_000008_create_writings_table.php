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
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('category_id')->nullable();
			$table->unsignedInteger('type_id')->nullable();
			$table->string('title');
			$table->string('slug')->unique();
			$table->text('text');
			$table->unsignedInteger('views')->default(0);
			$table->timestamps();
			$table->double('aura')->unsigned()->default(0);
            $table->timestamp('aura_updated_at')->nullable();
            $table->timestamp('home_posted_at')->nullable();
            $table->json('extra_info')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
			$table->foreign('category_id')->references('id')->on('categories')->onDelete('SET NULL');
			$table->foreign('type_id')->references('id')->on('types')->onDelete('SET NULL');
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
