<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The view has no readers left, and its `SELECT *` froze the columns
     * writings had when it was created.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS vwritings');
    }

    public function down(): void
    {
        DB::statement('CREATE VIEW vwritings
            AS
            SELECT *
            FROM writings
            LEFT join category_writing on writings.id = category_writing.writing_id;'
        );
    }
};
