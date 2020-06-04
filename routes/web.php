<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication
Auth::routes(['verify' => true]);

// Generic
Route::get('/', 'HomeController@show')->name('home');
Route::redirect('/home', '/');
Route::get('/search', 'SearchController@show')->name('search');

// Writings
Route::get('/writings', 'WritingsController@index')->name('writings.index');
Route::get('/writings/create', 'WritingsController@create')->name('writings.create')->middleware('auth');
Route::post('/writings/create', 'WritingsController@store')->name('writings.store')->middleware('auth');
Route::get('/writings/random', 'WritingsController@random')->name('writings.random');
Route::get('/writings/{writing}', 'WritingsController@show')->name('writings.show');
Route::get('/writings/edit/{writing}', 'WritingsController@edit')->name('writings.edit')->middleware('auth');
Route::put('/writings/edit/{writing}', 'WritingsController@update')->name('writings.update')->middleware('auth');
Route::delete('/writings/delete/{writing}', 'WritingsController@destroy')->name('writings.destroy')->middleware('auth');

// Users
Route::get('/users', 'UsersController@index')->name('users.index');
Route::get('/users/{user}', 'UsersController@show')->name('users.show');
Route::get('/users/{user}/writings', 'UsersWritingsController@index')->name('users_writings.index');
Route::get('/users/{user}/shelf', 'UsersShelvesController@index')->name('users_shelves.index');
Route::get('/users/{user}/hood', 'UsersHoodsController@index')->name('users_hoods.index');
Route::get('/users/{user}/hood/writings', 'UsersHoodsWritingsController@index')->name('users_hoods_writings.index');
Route::get('/users/edit/{user}', 'UsersController@edit')->name('users.edit')->middleware('auth');
Route::put('/users/edit/{user}', 'UsersController@update')->name('users.update')->middleware('auth');
Route::delete('/users/delete/{user}', 'UsersController@destroy')->name('users.destroy')->middleware('auth');

//Pages
Route::get('/pages/{page}', 'PagesController@show')->name('pages.show');

// Categories
Route::get('/categories', 'CategoriesController@index')->name('categories.index');
Route::get('/categories/{category}', 'CategoriesController@show')->name('categories.show');

// Types
Route::get('/types', 'TypesController@index')->name('types.index');
Route::get('/types/{type}', 'TypesController@show')->name('types.show');

// Tags
Route::get('/tags', 'TagsController@index')->name('tags.index');
Route::get('/tags/{tag}', 'TagsController@show')->name('tags.show');

// Comments and replies
Route::post('/replies/create', 'RepliesController@store')->name('replies.store')->middleware('auth');
Route::post('/comments/create', 'CommentsController@store')->name('comments.store')->middleware('auth');
Route::get('/comments/{writing}', 'CommentsController@index')->name('comments.index');

// Other user tasks
Route::post('/votes/store', 'VotesController@store')->name('votes.store')->middleware('auth');
Route::post('/shelves/store', 'ShelvesController@store')->name('shelves.store')->middleware('auth');
Route::post('/hoods/store', 'HoodsController@store')->name('hoods.store')->middleware('auth');

// Debugging SQL queries
if (env('APP_DEBUG')) {
    DB::listen(function($sql) {
        Log::info($sql->sql);
        Log::info($sql->bindings);
        Log::info($sql->time);
    });
}
