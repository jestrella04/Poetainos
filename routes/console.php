<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('aura-update', function () {
    DB::table('users')
        ->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $user = User::find($user->id);
                Http::put(route("api.aura.update", $user));
            }
        });
});

Artisan::command('karma-update', function () {
    DB::table('users')
        ->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $user = User::find($user->id);
                Http::put(route("api.karma.update", $user));
            }
        });
});
