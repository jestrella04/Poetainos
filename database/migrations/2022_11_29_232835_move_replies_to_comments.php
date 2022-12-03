<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MoveRepliesToComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\Reply::all()->each(function ($reply) {
            DB::table('comments')->insert([
                'user_id' => $reply->author->id,
                'writing_id' => $reply->comment->writing->id,
                'message' => '@' . $reply->comment->author->username . ' ' . $reply->message,
                'created_at' => $reply->created_at,
                'updated_at' => $reply->updated_at,
            ]);

            $reply->delete();
        });
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
