<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ComplaintsController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\HoodsController;
use App\Http\Controllers\InitController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PushNotificationsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShelvesController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UsersController;
//use App\Http\Controllers\UsersHoodsController;
//use App\Http\Controllers\UsersHoodsWritingsController;
use App\Http\Controllers\UsersNotificationsController;
use App\Http\Controllers\WritingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* Authentication routes */

require __DIR__ . '/auth.php';

/* Administration */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::get('categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('tags', [AdminController::class, 'tags'])->name('tags');
    Route::get('pages', [AdminController::class, 'pages'])->name('pages');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::get('writings', [AdminController::class, 'writings'])->name('writings');
    Route::get('tools', [AdminController::class, 'tools'])->name('tools');
    Route::get('complaints', [AdminController::class, 'complaints'])->name('complaints');
    Route::get('websockets', [AdminController::class, 'websockets'])->name('websockets');
    Route::get('analytics', [AdminController::class, 'analytics'])->name('analytics');

    Route::put('settings/edit', [SettingsController::class, 'update'])->name('settings.edit');
    Route::put('categories/edit', [CategoriesController::class, 'update'])->name('categories.edit');
    Route::put('tags/edit', [TagsController::class, 'update'])->name('tags.edit');
    Route::put('pages/edit', [PagesController::class, 'update'])->name('pages.edit');
    Route::put('complaints', [ComplaintsController::class, 'update'])->name('complaints.edit');

    Route::delete('categories/delete/{category}', [CategoriesController::class, 'destroy'])->name('categories.destroy');
    Route::delete('tags/delete/{tag}', [TagsController::class, 'destroy'])->name('tags.destroy');
    Route::delete('pages/delete/{page}', [PagesController::class, 'destroy'])->name('pages.destroy');
    Route::delete('users/delete/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::delete('writings/delete/{writing}', [WritingsController::class, 'destroy'])->name('writings.destroy');
});

/* Non public routes */
Route::middleware(['verified'])->group(function () {
    // Writings
    Route::get('/writings/create', [WritingsController::class, 'create'])->name('writings.create');
    Route::post('/writings/create', [WritingsController::class, 'store'])->name('writings.store');
    Route::get('/writings/edit/{writing}', [WritingsController::class, 'edit'])->name('writings.edit');
    Route::put('/writings/edit/{writing}', [WritingsController::class, 'update'])->name('writings.update');
    Route::delete('/writings/delete/{writing}', [WritingsController::class, 'destroy'])->name('writings.destroy');

    // Users
    Route::get('/users/edit/{user}', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/edit/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/delete/{user}', [UsersController::class, 'destroy'])->middleware('password.confirm')->name('users.destroy');
    Route::post('/users/query/{query}', [UsersController::class, 'query'])->name('users.query');
    Route::post('/users/block/{user}', [UsersController::class, 'blockUser'])->name('users.block');
    Route::get('/account', [UsersController::class, 'account'])->name('users.account');

    // Comments
    Route::post('/comments/create', [CommentsController::class, 'store'])->name('comments.store');
    Route::delete('/comments/delete/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');

    // Likes
    Route::post('/likes/{type}/{id}/store', [LikesController::class, 'store'])->name('likes.store');
    Route::delete('/likes/{type}/{id}/delete', [LikesController::class, 'destroy'])->name('likes.destroy');

    // Other user tasks
    Route::post('/shelves/{writing}/store', [ShelvesController::class, 'store'])->name('shelves.store');
    Route::delete('/shelves/{writing}/delete', [ShelvesController::class, 'destroy'])->name('shelves.destroy');
    Route::post('/hoods/store', [HoodsController::class, 'store'])->name('hoods.store');

    // Notifications
    Route::get('/notifications', [UsersNotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/show/{notification}', [UsersNotificationsController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/clear/read', [UsersNotificationsController::class, 'clear'])->name('notifications.clear');
    Route::post('/notifications/status', [UsersNotificationsController::class, 'status'])->name('notifications.status');
    Route::post('/notifications/email/{enable}', [UsersNotificationsController::class, 'email'])->name('notifications.email');

    // Push Subscriptions
    Route::post('subscriptions', [PushNotificationsController::class, 'update'])->name('push.update');
    Route::post('subscriptions/delete', [PushNotificationsController::class, 'destroy'])->name('push.delete');
});

/* Public routes */

// Installation
Route::get('/init', [InitController::class, 'show'])->name('init.show');
Route::post('/init', [InitController::class, 'init'])->name('init.init');
Route::get('/init/success', [InitController::class, 'success'])->name('init.success');

// Generic
Route::get('/manifest.json', [GenericController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [GenericController::class, 'offline'])->name('offline');
Route::get('/search', [SearchController::class, 'show'])->name('search');
Route::get('/explore', [GenericController::class, 'explore'])->name('explore');
Route::get('/sharer', [GenericController::class, 'sharer'])->name('sharer');

// Writings
Route::get('/', [WritingsController::class, 'index'])->name('home');
Route::get('/writings/awards', [WritingsController::class, 'index'])->name('writings.awards');
Route::get('/writings/random', [WritingsController::class, 'random'])->name('writings.random');
Route::get('/writings/{writing}', [WritingsController::class, 'show'])->name('writings.show');

// Users
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
Route::get('/users/{user}/writings', [GenericController::class, 'writings'])->name('users.writings.index');
Route::get('/users/{user}/shelf', [GenericController::class, 'shelf'])->name('users.shelf.index');
Route::get('/users/{user}/likes', [GenericController::class, 'likes'])->name('users.likes.index');
//Route::get('/users/{user}/hood', [UsersHoodsController::class, 'index'])->name('users.hood.index');
//Route::get('/users/{user}/hood/writings', [UsersHoodsWritingsController::class, 'index'])->name('users_hoods_writings.index');

// Pages
Route::get('/pages', [PagesController::class, 'index'])->name('pages.index');
Route::get('/pages/{page}', [PagesController::class, 'show'])->name('pages.show');

// Categories
//Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoriesController::class, 'show'])->name('categories.show');

// Tags
//Route::get('/tags', [TagsController::class, 'index'])->name('tags.index');
Route::get('/tags/query', [TagsController::class, 'query'])->name('tags.query');
Route::get('/tags/{tag}', [TagsController::class, 'show'])->name('tags.show');

// Comments
Route::get('/comments/{writing}', [CommentsController::class, 'index'])->name('comments.index');

// Contact form
Route::get('/contact', [ContactsController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactsController::class, 'store'])->name('contact.store');
Route::get('/reload-captcha', [ContactsController::class, 'reloadCaptcha'])->name('captcha.reload');

// Complaints
Route::get('/complaints/reasons', [ComplaintsController::class, 'reasons'])->name('complaints.reasons');
Route::post('/complaints/store', [ComplaintsController::class, 'store'])->name('complaints.store');

// Redirects, keep on the bottom
Route::redirect('/socialite', '/login', 301);
Route::redirect('/home', '/', 301);
Route::redirect('/writings', '/', 301);
