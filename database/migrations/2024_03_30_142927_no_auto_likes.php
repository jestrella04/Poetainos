<?php

use App\Models\Like;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $likes = Like::where('likeable_type', 'App\Models\Writing')->get();

        foreach ($likes as $like) {
            if (null !== $like->likeable) {
                $likeable_author_id = $like->likeable->user_id;
                $liker_id = $like->user_id;

                if ($likeable_author_id === $liker_id) {
                    $like->delete();
                }
            } else {
                $like->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
