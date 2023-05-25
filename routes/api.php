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
        // Friends
        Route::prefix('friends')->group(function () {
            Route::prefix('/requests')->group(function () {
                Route::get('/', 'getFriendsRequests');
                Route::get('/pending', 'getPendingFriendsRequests');
                Route::post('/send/{user}', 'sendFriendRequest');
                Route::post('/deny/{user}', 'denyFriendRequest');
                Route::post('/accept/{user}', 'acceptFriendRequest');
            });

            Route::get('/', 'getFriends');
            Route::get('/denied', 'getDeniedFriends');
            Route::get('/blocked', 'getBlockedFriends');
            Route::get('/available', 'getAvailableFriends');
            Route::post('/block/{user}', 'blockFriend');
            Route::post('/remove/{user}', 'removeFriend');
            Route::post('/unblock/{user}', 'unblockFriend');
        });

        Route::get('/', 'index')->middleware('isAdmin');
        Route::get('/{id}', 'show')->middleware('isAdminOrFriendOrSelf');
        Route::post('/set-fcm-token', 'setFcmToken');
        Route::post('/delete-fcm-token', 'deleteFcmToken');
        Route::post('/{id}', 'update')->middleware('isAdminOrSelf');
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
