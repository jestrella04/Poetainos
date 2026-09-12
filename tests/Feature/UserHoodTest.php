<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('hood relationship matches fellows via the correct pivot columns', function (): void {
    $user = User::factory()->create();
    $fellow = User::factory()->create();

    DB::table('hoods')->insert([
        'user_id' => $user->id,
        'fellow_user_id' => $fellow->id,
    ]);

    expect($user->hood()->pluck('users.id')->all())->toBe([$fellow->id]);
});
