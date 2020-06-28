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
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'AdminController@index')->name('index');
    Route::get('settings', 'AdminController@settings')->name('settings');
    Route::get('categories', 'AdminController@categories')->name('categories');
    Route::get('tags', 'AdminController@tags')->name('tags');
    Route::get('pages', 'AdminController@pages')->name('pages');
    Route::get('users', 'AdminController@users')->name('users');
    Route::get('writings', 'AdminController@writings')->name('writings');

    Route::put('settings/edit', 'SettingsController@update')->name('settings.edit');
    Route::put('categories/edit', 'CategoriesController@update')->name('categories.edit');
    Route::put('tags/edit', 'TagsController@update')->name('tags.edit');
    Route::put('pages/edit', 'PagesController@update')->name('pages.edit');

    Route::delete('categories/delete/{category}', 'CategoriesController@destroy')->name('categories.destroy');
    Route::delete('tags/delete/{tag}', 'TagsController@destroy')->name('tags.destroy');
    Route::delete('pages/delete/{page}', 'PagesController@destroy')->name('pages.destroy');
    Route::delete('users/delete/{user}', 'UsersController@destroy')->name('users.destroy');
    Route::delete('writings/delete/{writing}', 'WritingsController@destroy')->name('writings.destroy');
});

/* Non public routes */
Route::middleware(['verified'])->group(function () {
    // Writings
    Route::get('/writings/create', 'WritingsController@create')->name('writings.create');
    Route::post('/writings/create', 'WritingsController@store')->name('writings.store');
    Route::get('/writings/edit/{writing}', 'WritingsController@edit')->name('writings.edit');
    Route::put('/writings/edit/{writing}', 'WritingsController@update')->name('writings.edit');
    Route::delete('/writings/delete/{writing}', 'WritingsController@destroy')->name('writings.destroy');

    // Users
    Route::get('/users/edit/{user}', 'UsersController@edit')->name('users.edit');
    Route::put('/users/edit/{user}', 'UsersController@update')->name('users.edit');
    Route::delete('/users/delete/{user}', 'UsersController@destroy')->middleware('password.confirm')->name('users.destroy');

    // Comments and replies
    Route::post('/replies/create', 'RepliesController@store')->name('replies.store');
    Route::post('/comments/create', 'CommentsController@store')->name('comments.store');

    // Other user tasks
    Route::post('/votes/store', 'VotesController@store')->name('votes.store');
    Route::post('/shelves/store', 'ShelvesController@store')->name('shelves.store');
    Route::post('/hoods/store', 'HoodsController@store')->name('hoods.store');
});

/* Public routes */

// PWA manifest
Route::get('/static/json/pwa-manifest.json', 'ResourcesController@pwaManifest')->name('pwa.manifest');

// Social authentication
Route::get('/login/{service}', 'SocialAuthController@redirectToProvider')->name('social.login');
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
//Route::get('/categories', 'CategoriesController@index')->name('categories.index');
Route::get('/categories/{category}', 'CategoriesController@show')->name('categories.show');

// Tags
//Route::get('/tags', 'TagsController@index')->name('tags.index');
Route::get('/tags/{tag}', 'TagsController@show')->name('tags.show');

// Comments and replies
Route::get('/comments/{writing}', 'CommentsController@index')->name('comments.index');
