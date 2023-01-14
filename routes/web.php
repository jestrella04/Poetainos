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
    Route::get('/', 'App\Http\Controllers\AdminController@index')->name('index');
    Route::get('settings', 'App\Http\Controllers\AdminController@settings')->name('settings');
    Route::get('categories', 'App\Http\Controllers\AdminController@categories')->name('categories');
    Route::get('tags', 'App\Http\Controllers\AdminController@tags')->name('tags');
    Route::get('pages', 'App\Http\Controllers\AdminController@pages')->name('pages');
    Route::get('users', 'App\Http\Controllers\AdminController@users')->name('users');
    Route::get('writings', 'App\Http\Controllers\AdminController@writings')->name('writings');
    Route::get('tools', 'App\Http\Controllers\AdminController@tools')->name('tools');
    Route::get('complaints', 'App\Http\Controllers\AdminController@complaints')->name('complaints');

    Route::put('settings/edit', 'App\Http\Controllers\SettingsController@update')->name('settings.edit');
    Route::put('categories/edit', 'App\Http\Controllers\CategoriesController@update')->name('categories.edit');
    Route::put('tags/edit', 'App\Http\Controllers\TagsController@update')->name('tags.edit');
    Route::put('pages/edit', 'App\Http\Controllers\PagesController@update')->name('pages.edit');
    Route::put('complaints', 'App\Http\Controllers\ComplaintsController@update')->name('complaints.edit');

    Route::delete('categories/delete/{category}', 'App\Http\Controllers\CategoriesController@destroy')->name('categories.destroy');
    Route::delete('tags/delete/{tag}', 'App\Http\Controllers\TagsController@destroy')->name('tags.destroy');
    Route::delete('pages/delete/{page}', 'App\Http\Controllers\PagesController@destroy')->name('pages.destroy');
    Route::delete('users/delete/{user}', 'App\Http\Controllers\UsersController@destroy')->name('users.destroy');
    Route::delete('writings/delete/{writing}', 'App\Http\Controllers\WritingsController@destroy')->name('writings.destroy');
});

/* Non public routes */
Route::middleware(['verified'])->group(function () {
    // Writings
    Route::get('/writings/create', 'App\Http\Controllers\WritingsController@create')->name('writings.create');
    Route::post('/writings/create', 'App\Http\Controllers\WritingsController@store')->name('writings.store');
    Route::get('/writings/edit/{writing}', 'App\Http\Controllers\WritingsController@edit')->name('writings.edit');
    Route::put('/writings/edit/{writing}', 'App\Http\Controllers\WritingsController@update')->name('writings.update');
    Route::delete('/writings/delete/{writing}', 'App\Http\Controllers\WritingsController@destroy')->name('writings.destroy');

    // Users
    Route::get('/users/edit/{user}', 'App\Http\Controllers\UsersController@edit')->name('users.edit');
    Route::put('/users/edit/{user}', 'App\Http\Controllers\UsersController@update')->name('users.update');
    Route::delete('/users/delete/{user}', 'App\Http\Controllers\UsersController@destroy')->middleware('password.confirm')->name('users.destroy');
    Route::post('/users/query/{query}', 'App\Http\Controllers\UsersController@query')->name('users.query');
    Route::get('/users/block/{user}', 'App\Http\Controllers\UsersController@promptBeforeBlock')->name('users.block.confirm');
    Route::post('/users/block/{user}', 'App\Http\Controllers\UsersController@blockUser')->name('users.block.confirmed');

    // Comments
    Route::post('/comments/create', 'App\Http\Controllers\CommentsController@store')->name('comments.store');
    Route::delete('/comments/delete/{comment}', 'App\Http\Controllers\CommentsController@destroy')->name('comments.destroy');

    // Likes
    Route::post('/likes/{type}/{id}/store', 'App\Http\Controllers\LikesController@store')->name('likes.store');
    Route::delete('/likes/{type}/{id}/delete', 'App\Http\Controllers\LikesController@destroy')->name('likes.destroy');

    // Other user tasks
    Route::post('/shelves/{writing}/store', 'App\Http\Controllers\ShelvesController@store')->name('shelves.store');
    Route::delete('/shelves/{writing}/delete', 'App\Http\Controllers\ShelvesController@destroy')->name('shelves.destroy');
    Route::post('/hoods/store', 'App\Http\Controllers\HoodsController@store')->name('hoods.store');

    // Notifications
    Route::get('/notifications/list/unread', 'App\Http\Controllers\UsersNotificationsController@listUnread')->name('notifications.list.unread');
    Route::get('/notifications/list/all', 'App\Http\Controllers\UsersNotificationsController@listAll')->name('notifications.list.all');
    Route::get('/notifications/read/{notification}', 'App\Http\Controllers\UsersNotificationsController@read')->name('notifications.read');
    Route::post('/notifications/read/all', 'App\Http\Controllers\UsersNotificationsController@clear')->name('notifications.clear');

    // Push Subscriptions
    Route::post('subscriptions', 'App\Http\Controllers\PushNotificationsController@update')->name('push.update');
    Route::post('subscriptions/delete', 'App\Http\Controllers\PushNotificationsController@destroy')->name('push.delete');
});

/* Public routes */

// Installation
Route::get('/init', 'App\Http\Controllers\InitController@show')->name('init.show');
Route::post('/init', 'App\Http\Controllers\InitController@init')->name('init.init');
Route::get('/init/success', 'App\Http\Controllers\InitController@success')->name('init.success');

// PWA manifest
Route::get('/manifest.json', 'App\Http\Controllers\ResourcesController@pwaManifest')->name('pwa.manifest');

// Social authentication
Route::get('/login/{service}', 'App\Http\Controllers\SocialAuthController@redirectToProvider')->name('social.login');
Route::get('/login/{service}/callback', 'App\Http\Controllers\SocialAuthController@handleProviderCallback');

// Generic
Route::get('/offline', 'App\Http\Controllers\HomeController@offline')->name('offline');
Route::get('/search', 'App\Http\Controllers\SearchController@show')->name('search');
Route::get('/explore','App\Http\Controllers\HomeController@explore')->name('explore');
Route::get('/socialite','App\Http\Controllers\HomeController@socialite')->name('socialite')->middleware('guest');
Route::get('/sharer', 'App\Http\Controllers\HomeController@sharer')->name('sharer');

// Writings
Route::get('/', 'App\Http\Controllers\WritingsController@index')->name('home');
Route::get('/writings/awards', 'App\Http\Controllers\GoldenFlowersController@index')->name('writings.awards');
Route::get('/writings/random', 'App\Http\Controllers\WritingsController@random')->name('writings.random');
Route::get('/writings/{writing}', 'App\Http\Controllers\WritingsController@show')->name('writings.show');

// Users
Route::get('/users', 'App\Http\Controllers\UsersController@index')->name('users.index');
Route::get('/users/{user}', 'App\Http\Controllers\UsersController@show')->name('users.show');
Route::get('/users/{user}/writings', 'App\Http\Controllers\UsersWritingsController@index')->name('users_writings.index');
Route::get('/users/{user}/shelf', 'App\Http\Controllers\UsersShelvesController@index')->name('users_shelves.index');
Route::get('/users/{user}/hood', 'App\Http\Controllers\UsersHoodsController@index')->name('users_hoods.index');
Route::get('/users/{user}/hood/writings', 'App\Http\Controllers\UsersHoodsWritingsController@index')->name('users_hoods_writings.index');

// Pages
Route::get('/pages/{page}', 'App\Http\Controllers\PagesController@show')->name('pages.show');

// Categories
//Route::get('/categories', 'App\Http\Controllers\CategoriesController@index')->name('categories.index');
Route::get('/categories/{category}', 'App\Http\Controllers\CategoriesController@show')->name('categories.show');

// Tags
//Route::get('/tags', 'App\Http\Controllers\TagsController@index')->name('tags.index');
Route::get('/tags/query', 'App\Http\Controllers\TagsController@query')->name('tags.query');
Route::get('/tags/{tag}', 'App\Http\Controllers\TagsController@show')->name('tags.show');

// Comments
Route::get('/comments/{writing}', 'App\Http\Controllers\CommentsController@index')->name('comments.index');

// Contact form
Route::get('/contact', 'App\Http\Controllers\ContactsController@create')->name('contact.create');
Route::post('/contact', 'App\Http\Controllers\ContactsController@store')->name('contact.store');

// Complaints
Route::get('/complaints/{type}/{id}/create', 'App\Http\Controllers\ComplaintsController@create')->name('complaints.create');
Route::post('/complaints/store', 'App\Http\Controllers\ComplaintsController@store')->name('complaints.store');

// Redirects, keep on the bottom
Route::redirect('/home', '/', 301);
Route::redirect('/writings', '/', 301);
