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
Route::get('tweet-log', 'AdminController@tweetLog');
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

// Leaderboard routes
Route::get('/leaderboard', 'LeaderboardController@index')->name('leaderboard');

/**
 * Auth Routes
 */
Route::group(['middleware' => ['auth']], function () {

    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
    Route::get('user/{urlSegment?}', 'UserController@profile');

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
});

Route::prefix('api')->group(function () {
    Route::get('games/{date?}', 'GamesController@gamesJson');
    Route::get('game/{urlSegment}', 'GamesController@gameJson');
});