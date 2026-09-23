<?php

use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by bootstrap/app.php within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [UsersController::class, 'me'])->name('api.user.show');
    Route::put('/karma/{user}', [UsersController::class, 'recalculateKarma'])->name('api.karma.update');
});
