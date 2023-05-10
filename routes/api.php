<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('user', 'AuthController@user');
        Route::post('logout', 'AuthController@logout');
    });
});

// Users
Route::group(['middleware' => 'auth:api'], function () {
    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/', 'index')->middleware('isAdmin');
        Route::get('/friends', 'getFriends');
        Route::get('/friend-requests', 'getFriendRequests');
        Route::get('/not-requested-users', 'getNotRequestedUsers');
        Route::get('/pending-friend-request', 'getPendingFriendRequests');
        Route::get('/{id}', 'show')->middleware('isAdminOrFriendOrSelf');

        Route::post('/set-fcm-token', 'setFcmToken');
        Route::post('/delete-fcm-token', 'deleteFcmToken');
        Route::post('/remove-friend/{user}', 'removeFriend');
        Route::post('/{id}', 'update')->middleware('isAdminOrSelf');
        Route::post('/send-friend-request/{user}', 'sendFriendRequest');
        Route::post('/accept-friend-request/{user}', 'acceptFriendRequest');
        Route::post('/deny-friend-request/{user}', 'denyFriendRequest');
    });

    Route::prefix('chat')->controller(ChatController::class)->group(function () {
        Route::prefix('messages')->group(function () {
            Route::post('/send', 'sendMessage');
            Route::get('/{recipient}', 'getMessages');
            Route::patch('/{sender}/read-prev-messages', 'markPrevMessagesRead');
            Route::post('/edit/{message}', 'editMessage')->middleware('isAdminOrSelf');
            Route::delete('delete/{message}', 'deleteMessage')->middleware('isAdminOrSelf');
        });
    });
});
