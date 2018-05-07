<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

/**
 * User routes
 * DELETE /logout
 * GET /user
 * PUT /user/password
 * GET /users
 * GET /users/{id}
 * 
 * 
 */

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1'
], function ($api) {
    // create token
    $api->post('auth', [
        'as' => 'auth.store',
        'uses' => 'AuthController@store',
    ]);

    // register
    $api->post('register', [
        'as' => 'auth.register',
        'uses' => 'AuthController@register',
    ]);

    // refresh jwt token
    $api->put('auth/refresh', [
        'as' => 'auth.update',
        'uses' => 'AuthController@update',
    ]);

    // need authentication
    $api->group(['middleware' => 'api.auth'], function ($api) {
        // logout
        $api->delete('logout', [
            'as' => 'auth.logout',
            'uses' => 'AuthController@destroy',
        ]);

        // User show
        $api->get('user', [
            'as' => 'user.show',
            'uses' => 'UserController@userShow',
        ]);

        // update my password
        $api->put('user/password', [
            'as' => 'user.password.update',
            'uses' => 'UserController@updatePassword',
        ]);

        // USER
        // user list
        $api->get('users', [
            'as' => 'users.index',
            'uses' => 'UserController@index',
        ]);

        // user detail
        $api->get('users/{id}', [
            'as' => 'users.show',
            'uses' => 'UserController@show',
        ]);

        // user update
        $api->put('users/{id}', [
            'as' => 'users.update',
            'uses' => 'UserController@update',
        ]);

        // user delete
        $api->delete('users/{id}', [
            'as' => 'users.destroy',
            'uses' => 'UserController@destroy',
        ]);

        // Event
        // event list
        $api->get('events', [
            'as' => 'events.index',
            'uses' => 'EventController@index',
        ]);

        // Event detail
        $api->get('events/{id}', [
            'as' => 'events.show',
            'uses' => 'EventController@show',
        ]);

        // user's events index
        $api->get('user/events', [
            'as' => 'user.events.index',
            'uses' => 'UserController@userEvents',
        ]);

        // create a event
        $api->post('events', [
            'as' => 'events.store',
            'uses' => 'EventController@store',
        ]);

        // update a event
        $api->put('events/{id}', [
            'as' => 'events.update',
            'uses' => 'EventController@update',
        ]);

        // delete a Event
        $api->delete('events/{id}', [
            'as' => 'events.destroy',
            'uses' => 'EventController@destroy',
        ]);

        // DESTINIES
        //List Destinies
        $api->get('destiny', [
            'as' => 'destiny.index',
            'uses' => 'DestinyController@index',
        ]);
        // Event of destiny
        $api->get('destiny/{id}/events', [
            'as' => 'destiny.events',
            'uses' => 'DestinyController@events',
        ]);
    });
});