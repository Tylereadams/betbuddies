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

// Normal Routes
Route::get('/', 'PagesController@index');
Route::get('/home', 'HomeController@index')->name('home');

Route::get('machine-learning', 'MachineLearningController@index');
Route::post('tweets/{tweetId}/save', 'MachineLearningController@store');


Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

Route::get('playersearch', 'PlayersController@jsonSearch');

// Game routes
Route::get('/game/{urlSegment}', 'GamesController@game')->name('game');
Route::get('/games/{date?}', 'GamesController@gamesByDate')->name('games');
Route::get('/game/{urlSegment}/image', 'GamesController@image');


// Leaderboard routes
Route::get('/leaderboard', 'LeaderboardController@index')->name('leaderboard');

// User routes
Route::get('user/{urlSegment?}', 'UserController@profile');

// Samsara route
Route::get('samsara', 'SamsaraController@getTripsData');


// Admin Routes
Route::get('tweet-log', 'AdminController@tweetLog');

/**
 * Auth Routes
 */
Route::group(['middleware' => ['auth']], function () {

    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    // Bets

    Route::post('/game/{urlSegment}', 'BetsController@store');

    // Password Reset Routes...
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::prefix('api')->group(function () {
        Route::post('game/{urlSegment}/bet', 'BetsController@store');
        Route::post('bet/{usersBets}/accept', 'BetsController@accept');
        Route::delete('bet/{usersBets}', 'BetsController@delete');
    });

    // Admin functions
    Route::group(['middleware' => ['admin']], function() {
        Route::get('team/edit', 'AdminController@editTeamTwitter');
        Route::post('team/{team}/edit', 'AdminController@saveTwitterData');

        Route::get('login/twitter', 'AdminController@redirectToProvider');
        Route::get('login/twitter/callback', 'AdminController@handleProviderCallback');
    });
});

Route::prefix('api')->group(function () {
    Route::get('games/{date?}', 'GamesController@gamesJson');
    Route::get('game/{urlSegment}', 'GamesController@gameJson');
});