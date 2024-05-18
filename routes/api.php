<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::put('/aura/{user}', function (User $user) {
    $user->updateAura();
    return response($user->aura);
})->name('api.aura.update');

Route::put('/karma/{user}', function (User $user) {
    $user->updateKarma();
    return response($user->karma);
})->name('api.karma.update');
