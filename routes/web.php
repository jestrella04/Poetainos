<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlockedUsersController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ComplaintsController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PendingEmailController;
use App\Http\Controllers\PushNotificationsController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShelvesController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UserListingsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersNotificationsController;
use App\Http\Controllers\WritingsController;
use App\Http\Middleware\RecordAdminActions;
use Illuminate\Routing\RedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* Authentication routes */

require __DIR__.'/auth.php';

/* Administration */
Route::middleware(['auth', 'admin', RecordAdminActions::class])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::get('categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('tags', [AdminController::class, 'tags'])->name('tags');
    Route::get('pages', [AdminController::class, 'pages'])->name('pages');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::get('writings', [AdminController::class, 'writings'])->name('writings');
    Route::get('logs', [AdminController::class, 'logs'])->name('logs');
    Route::get('logs/entries', [AdminController::class, 'logEntries'])->name('logs.entries');
    Route::get('logs/{file}/download', [AdminController::class, 'downloadLog'])->where('file', '[\w.\-]+\.log')->name('logs.download');
    Route::get('complaints', [AdminController::class, 'complaints'])->name('complaints');
    Route::get('activity', [AdminController::class, 'activity'])->name('activity');
    Route::get('analytics', [AdminController::class, 'analytics'])->name('analytics');

    Route::put('settings', [SettingsController::class, 'update'])->name('settings.edit');
    Route::post('categories', [CategoriesController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoriesController::class, 'update'])->name('categories.update');
    Route::post('pages', [PagesController::class, 'store'])->name('pages.store');
    Route::put('pages/{page}', [PagesController::class, 'update'])->name('pages.update');
    Route::put('complaints/{complaint}/close', [ComplaintsController::class, 'close'])->name('complaints.close');

    Route::delete('categories/{category}', [CategoriesController::class, 'destroy'])->name('categories.destroy');
    Route::delete('tags/{tag}', [TagsController::class, 'destroy'])->name('tags.destroy');
    Route::delete('pages/{page}', [PagesController::class, 'destroy'])->name('pages.destroy');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->middleware('password.confirm')->name('users.destroy');
    Route::delete('writings/{writing}', [WritingsController::class, 'destroy'])->name('writings.destroy');
    Route::delete('logs/{file}', [AdminController::class, 'clearLog'])->where('file', '[\w.\-]+\.log')->name('logs.clear');
});

/* Non public routes */
Route::middleware(['auth', 'verified'])->group(function (): void {
    // Writings
    Route::get('/writings/create', [WritingsController::class, 'create'])->name('writings.create');
    Route::post('/writings', [WritingsController::class, 'store'])->name('writings.store');
    Route::get('/writings/edit/{writing}', [WritingsController::class, 'edit'])->name('writings.edit');
    Route::put('/writings/{writing}', [WritingsController::class, 'update'])->name('writings.update');
    Route::delete('/writings/{writing}', [WritingsController::class, 'destroy'])->name('writings.destroy');

    // Users
    Route::get('/users/edit/{user}', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->middleware('password.confirm')->name('users.destroy');
    Route::get('/users/query', [UsersController::class, 'suggest'])->name('users.query');
    Route::post('/users/block/{user}', [BlockedUsersController::class, 'store'])->name('users.block');
    Route::delete('/users/block/{user}', [BlockedUsersController::class, 'destroy'])->name('users.unblock');
    Route::get('/account', [UsersController::class, 'account'])->name('users.account');
    Route::get('/account/blocked', [BlockedUsersController::class, 'index'])->name('users.blocked.index');
    Route::post('/account/email/verify', [PendingEmailController::class, 'confirm'])->middleware('throttle:6,1')->name('users.email.verify');
    Route::post('/account/email/resend', [PendingEmailController::class, 'resend'])->middleware('throttle:6,1')->name('users.email.resend');
    Route::delete('/account/email/pending', [PendingEmailController::class, 'destroy'])->name('users.email.cancel');

    // Comments
    Route::post('/comments', [CommentsController::class, 'store'])->middleware('throttle:20,1')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');

    // Likes
    Route::post('/likes/{likeable}/{likeableId}/toggle', [LikesController::class, 'toggle'])->middleware('throttle:60,1')->name('likes.toggle');

    // Other user tasks
    Route::post('/shelves/{writing}/toggle', [ShelvesController::class, 'toggle'])->middleware('throttle:60,1')->name('shelves.toggle');

    // Notifications
    Route::get('/notifications', [UsersNotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/show/{notification}', [UsersNotificationsController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/clear/read', [UsersNotificationsController::class, 'clear'])->name('notifications.clear');
    Route::put('/notifications/email', [UsersNotificationsController::class, 'setEmailPreference'])->name('notifications.email');

    // Push Subscriptions
    Route::post('subscriptions', [PushNotificationsController::class, 'update'])->name('push.update');
    Route::delete('subscriptions', [PushNotificationsController::class, 'destroy'])->name('push.delete');
});

/* Public routes */

// Generic
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [PwaController::class, 'offline'])->name('offline');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

// Writings
Route::get('/', [WritingsController::class, 'home'])->name('home');
Route::get('/writings/awards', [WritingsController::class, 'awards'])->name('writings.awards');
Route::get('/writings/random', [WritingsController::class, 'random'])->name('writings.random');
Route::get('/writings/{writing}', [WritingsController::class, 'show'])->name('writings.show');

// Users
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
Route::get('/users/{user}/writings', [UserListingsController::class, 'writings'])->name('users.writings.index');
Route::get('/users/{user}/shelf', [UserListingsController::class, 'shelf'])->name('users.shelf.index');
Route::get('/users/{user}/likes', [UserListingsController::class, 'likes'])->name('users.likes.index');

// Pages
Route::get('/pages', [PagesController::class, 'index'])->name('pages.index');
Route::get('/pages/{page}', [PagesController::class, 'show'])->name('pages.show');

// Categories
Route::get('/categories/{category}', [CategoriesController::class, 'show'])->name('categories.show');

// Tags
Route::get('/tags/query', [TagsController::class, 'search'])->name('tags.query');
Route::get('/tags/{tag}', [TagsController::class, 'show'])->name('tags.show');

// Comments
Route::get('/writings/{writing}/comments', [CommentsController::class, 'index'])->name('comments.index');

// Contact form
Route::get('/contact', [ContactsController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactsController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

// Complaints
Route::get('/complaints/reasons', [ComplaintsController::class, 'reasons'])->name('complaints.reasons');
Route::post('/complaints', [ComplaintsController::class, 'store'])->middleware('throttle:complaints')->name('complaints.store');

// Redirects, keep on the bottom
Route::redirect('/socialite', '/login', 301);
Route::redirect('/home', '/', 301);
// GET only: Route::redirect() answers every method, which would take POST /writings from writings.store
Route::get('/writings', RedirectController::class)->defaults('destination', '/')->defaults('status', 301);
