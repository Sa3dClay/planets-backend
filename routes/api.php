<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('user', 'AuthController@user');
        Route::post('logout', 'AuthController@logout');
    });
});

// Users
Route::group(['middleware' => 'auth:api'], function() {
    Route::get('users', 'UserController@index')->middleware('isAdmin');
    Route::get('users/{id}', 'UserController@show')->middleware('isAdminOrSelf');
    Route::post('users/{id}', 'UserController@update')->middleware('isAdminOrSelf');

    Route::get('getMessages', 'ChatController@getMessages');
    Route::post('sendMessage', 'ChatController@sendMessage');

    Route::post('editMessage/{id}', 'ChatController@editMessage')->middleware('isAdminOrSelf');
    Route::post('deleteMessage/{id}', 'ChatController@deleteMessage')->middleware('isAdminOrSelf');
});
