<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notifications')->where('notifiable_type', 'App\User')->update(['notifiable_type' => 'App\Models\User']);
        DB::table('complaints')->where('complainable_type', 'App\User')->update(['complainable_type' => 'App\Models\User']);
        DB::table('complaints')->where('complainable_type', 'App\Writing')->update(['complainable_type' => 'App\Models\Writing']);
        DB::table('complaints')->where('complainable_type', 'App\Comment')->update(['complainable_type' => 'App\Models\Comment']);
        DB::table('push_subscriptions')->where('subscribable_type', 'App\User')->update(['subscribable_type' => 'App\Models\User']);
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
};
