<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateWritingsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS vwritings');

        DB::statement('CREATE VIEW vwritings
            AS
            SELECT *
            FROM writings
            LEFT join category_writing on writings.id = category_writing.writing_id;'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS vwritings');
    }
}
