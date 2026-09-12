<?php

use Illuminate\Database\Migrations\Migration;

class MoveRepliesToComments extends Migration
{
    /**
     * Run the migrations.
     *
     * This one-time data migration (moving legacy Reply records into the
     * comments table) has already been completed against production data.
     * The Reply model no longer exists, so this is intentionally a no-op.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
