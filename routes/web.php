<?php

use Illuminate\Support\Arr;
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
    Route::get('tools', 'AdminController@tools')->name('tools');

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
    Route::put('/writings/edit/{writing}', 'WritingsController@update')->name('writings.update');
    Route::delete('/writings/delete/{writing}', 'WritingsController@destroy')->name('writings.destroy');

    // Users
    Route::get('/users/edit/{user}', 'UsersController@edit')->name('users.edit');
    Route::put('/users/edit/{user}', 'UsersController@update')->name('users.update');
    Route::delete('/users/delete/{user}', 'UsersController@destroy')->middleware('password.confirm')->name('users.destroy');

    // Comments and replies
    Route::post('/replies/create', 'RepliesController@store')->name('replies.store');
    Route::post('/comments/create', 'CommentsController@store')->name('comments.store');

    // Other user tasks
    Route::post('/votes/store', 'VotesController@store')->name('votes.store');
    Route::post('/shelves/store', 'ShelvesController@store')->name('shelves.store');
    Route::post('/hoods/store', 'HoodsController@store')->name('hoods.store');

    // Notifications
    Route::get('/notifications', 'UsersNotificationsController@index')->name('notifications.index');
    Route::get('/notifications/read/{notification}', 'UsersNotificationsController@read')->name('notifications.read');
    Route::post('/notifications/read/all', 'UsersNotificationsController@clear')->name('notifications.clear');

    // Push Subscriptions
    Route::post('subscriptions', 'PushNotificationsController@update')->name('push.update');
    Route::post('subscriptions/delete', 'PushNotificationsController@destroy')->name('push.delete');
});

/* Public routes */

// Installation
Route::get('/init', 'InitController@show')->name('init.show');
Route::post('/init', 'InitController@init')->name('init.init');
Route::get('/init/success', 'InitController@success')->name('init.success');

// PWA manifest
Route::get('/manifest.json', 'ResourcesController@pwaManifest')->name('pwa.manifest');

// Social authentication
Route::get('/login/{service}', 'SocialAuthController@redirectToProvider')->name('social.login');
Route::get('/login/{service}/callback', 'SocialAuthController@handleProviderCallback');

// Generic
Route::get('/offline', 'HomeController@offline')->name('offline');
Route::get('/search', 'SearchController@show')->name('search');

// Writings
Route::get('/', 'WritingsController@index')->name('home');
Route::get('/writings/awards', 'GoldenFlowersController@index')->name('writings.awards');
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

// Contact form
Route::get('/contact', 'ContactsController@create')->name('contact.create');
Route::post('/contact', 'ContactsController@store')->name('contact.store');

// Redirects
Route::redirect('/home', '/');
Route::redirect('/writings', '/');

// Test
Route::get('/test', function() {
    $u = \App\User::find('114');
    $a = [
        'user_id' => $u->id,
        'notifications' => [
            'unread' => $u->unreadNotifications()->count(),
            'total' => $u->notifications()->count(),
        ],
    ];
    event(new App\Events\NotificationEvent($a));
});
