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
Auth::routes();

// Generic
Route::get('/', 'HomeController@show')->name('home');
Route::get('/search', 'SearchController@show')->name('search');

// Writings
Route::get('/writings', 'WritingsController@index')->name('writings.index');
Route::get('/writings/create', 'WritingsController@create')->name('writings.create')->middleware('auth');
Route::post('/writings/create', 'WritingsController@store')->name('writings.store')->middleware('auth');
Route::get('/writings/random', 'WritingsController@random')->name('writings.random');
Route::get('/writings/{writing}', 'WritingsController@show')->name('writings.show');
Route::get('/writings/edit/{writing}', 'WritingsController@edit')->name('writings.edit')->middleware('auth');
Route::put('/writings/edit/{writing}', 'WritingsController@update')->name('writings.update')->middleware('auth');

// Users
Route::get('/users', 'UsersController@index')->name('users.index');
Route::get('/users/{user}', 'UsersController@show')->name('users.show');
Route::get('/users/{user}/writings', 'UsersController@showWritings')->name('users_writings.show');
Route::get('/users/{user}/shelf', 'UsersController@showShelf')->name('users_shelves.show');
Route::get('/users/{user}/hood', 'UsersController@showHood')->name('users_hoods.show');
Route::get('/users/{user}/hood/writings', 'UsersController@showHoodWritings')->name('users_hoods_writings.show');

//Pages
Route::get('/pages', 'PagesController@index')->name('pages.index');
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
Route::post('/votes/create', 'VotesController@store')->name('votes.store')->middleware('auth');
Route::post('/shelves/create', 'ShelvesController@store')->name('shelves.store')->middleware('auth');
Route::post('/hoods/create', 'HoodsController@store')->name('hoods.store')->middleware('auth');

// Debugging SQL queries
if (env('APP_DEBUG')) {
    DB::listen(function($sql) {
        Log::info($sql->sql);
        Log::info($sql->bindings);
        Log::info($sql->time);
    });
}
