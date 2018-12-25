<?php

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

Route::group(['prefix' => '/'],function () {
    Route::get('login','Auth\LoginController@showLoginForm');
    Route::post('login',['as' => 'login', 'uses' => 'Auth\LoginController@login']);
    Route::get('logout','Auth\LoginController@logout');
    Route::get('register','Auth\RegisterController@showRegistrationForm');
    Route::post('register','Auth\RegisterController@register');
    Route::get('password/reset','Auth\ResetPasswordController@showResetForm');
    Route::post('password/reset','Auth\ResetPasswordController@reset');
});


Route::group([], function () {

    // Discussion
    Route::resource('discussion','DiscussionController', ['except' => 'destroy']);

    Route::post('password/change','UserController@changePassword')->middleware('auth');

    // Link
    Route::get('link', 'LinkController@index');

    // Article
    Route::get('/','ArticleController@index');
    Route::get('/{slug}','ArticleController@show');

    // Search
    Route::get('search', 'HomeController@search');

    // Category
    Route::get('category/{category}', 'CategoryController@show');
    Route::get('category', 'CategoryController@index');

    // Tag
    Route::get('tag', 'TagController@index');
    Route::get('tag/{tag}', 'TagController@show');

});

/* Dashboard Index */
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'admin']], function () {
    Route::get('{path?}', 'HomeController@dashboard')->where('path', '[\/\w\.-]*');
});

// Github Auth Route
Route::group(['prefix' => 'auth/github'], function () {
    Route::get('/', 'Auth\AuthController@redirectToProvider');
    Route::get('callback', 'Auth\AuthController@handleProviderCallback');
    Route::get('register', 'Auth\AuthController@create');
    Route::post('register', 'Auth\AuthController@store');
});

// User
Route::group(['prefix' => 'user'], function () {
    Route::get('/', 'UserController@index');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('profile', 'UserController@edit');
        Route::put('profile/{id}', 'UserController@update');
        Route::post('follow/{id}', 'UserController@doFollow');
        Route::get('notification', 'UserController@notifications');
        Route::post('notification', 'UserController@markAsRead');
    });

    Route::group(['prefix' => '{username}'], function () {
        Route::get('/', 'UserController@show');
        Route::get('comments', 'UserController@comments');
        Route::get('following', 'UserController@following');
        Route::get('discussions', 'UserController@discussions');
    });
});

// User Setting
Route::group(['middleware' => 'auth', 'prefix' => 'setting'], function () {
    Route::get('/', 'SettingController@index')->name('setting.index');
    Route::get('binding', 'SettingController@binding')->name('setting.binding');

    Route::get('notification', 'SettingController@notification')->name('setting.notification');
    Route::post('notification', 'SettingController@setNotification');
});


