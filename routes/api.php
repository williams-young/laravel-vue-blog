<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers', 'middleware' => 'api'], function ($api) {

        // routes that should be authorized
        $api->group(['middleware' => 'api.auth'], function ($api) {

            // user
            $api->get('test', 'UserController@test');
            $api->get('refresh', ['as' => 'tokens.refresh', 'uses' => 'UserController@refresh']);

        });

        $api->get('login', 'UserController@login');

        // File Upload
        $api->post('file/upload', 'UploadController@fileUpload')->middleware('auth:api');
        // Edit Avatar
        $api->post('crop/avatar', 'UserController@cropAvatar')->middleware('auth:api');

        // Comment
        $api->get('commentable/{commentableId}/comment', 'CommentController@show')->middleware('api');
        $api->post('comments', 'CommentController@store')->middleware('auth:api');
        $api->delete('comments/{id}', 'CommentController@destroy')->middleware('auth:api');
        $api->post('comments/vote/{type}', 'MeController@postVoteComment')->middleware('auth:api');
        $api->get('tags', 'TagController@getList');

    });
});


