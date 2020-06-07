<?php

use Illuminate\Support\Facades\Auth;
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

/* Authentication routes */

Auth::routes(['verify' => true]);

/* Administration */
Route::get('admin', 'AdminController@index')->name('admin.index');
Route::get('admin/types', 'AdminController@types')->name('admin.types');
Route::get('admin/categories', 'AdminController@categories')->name('admin.categories');
Route::get('admin/pages', 'AdminController@pages')->name('admin.pages');
Route::get('admin/users', 'AdminController@users')->name('admin.users');

/* Non public routes */

Route::middleware(['verified'])->group(function () {
    // Writings
    Route::get('/writings/create', 'WritingsController@create')->name('writings.create');
    Route::post('/writings/create', 'WritingsController@store')->name('writings.store');
    Route::get('/writings/edit/{writing}', 'WritingsController@edit')->name('writings.edit');
    Route::put('/writings/edit/{writing}', 'WritingsController@update')->name('writings.update');
    Route::delete('/writings/delete/{writing}', 'WritingsController@destroy')->name('writings.destroy');

    // Users
    Route::get('/users/edit/{user}', 'UsersController@edit')->name('users.edit');
    Route::put('/users/edit/{user}', 'UsersController@update')->name('users.update');
    Route::delete('/users/delete/{user}', 'UsersController@destroy')->name('users.destroy');

    // Comments and replies
    Route::post('/replies/create', 'RepliesController@store')->name('replies.store');
    Route::post('/comments/create', 'CommentsController@store')->name('comments.store');

    // Other user tasks
    Route::post('/votes/store', 'VotesController@store')->name('votes.store');
    Route::post('/shelves/store', 'ShelvesController@store')->name('shelves.store');
    Route::post('/hoods/store', 'HoodsController@store')->name('hoods.store');
});

/* Public routes */

// Social Authentication
Route::get('/login/{service}/redirect', 'SocialAuthController@redirectToProvider');
Route::get('/login/{service}/callback', 'SocialAuthController@handleProviderCallback');

// Generic
Route::get('/', 'HomeController@show')->name('home');
Route::redirect('/home', '/');
Route::get('/search', 'SearchController@show')->name('search');

// Writings
Route::get('/writings', 'WritingsController@index')->name('writings.index');
Route::get('/writings/random', 'WritingsController@random')->name('writings.random');
Route::get('/writings/{writing}', 'WritingsController@show')->name('writings.show');

// Users
Route::get('/users', 'UsersController@index')->name('users.index');
Route::get('/users/{user}', 'UsersController@show')->name('users.show');
Route::get('/users/{user}/writings', 'UsersWritingsController@index')->name('users_writings.index');
Route::get('/users/{user}/shelf', 'UsersShelvesController@index')->name('users_shelves.index');
Route::get('/users/{user}/hood', 'UsersHoodsController@index')->name('users_hoods.index');
Route::get('/users/{user}/hood/writings', 'UsersHoodsWritingsController@index')->name('users_hoods_writings.index');

// Pages
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
Route::get('/comments/{writing}', 'CommentsController@index')->name('comments.index');
