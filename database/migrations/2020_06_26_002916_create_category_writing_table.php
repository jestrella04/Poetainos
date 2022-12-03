<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryWritingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_writing', function (Blueprint $table) {
            $table->unsignedBigInteger('writing_id');
			$table->unsignedBigInteger('category_id');
            $table->primary(['writing_id','category_id']);

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('CASCADE');
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
        Schema::dropIfExists('category_writing');
    }
}
