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
Route::get('/games/{date?}', 'GamesController@gamesByDate')->name('games');

Route::get('machine-learning', 'MachineLearningController@index');
Route::get('tweet-log', 'AdminController@tweetLog');
Route::post('tweets/{tweetId}/save', 'MachineLearningController@store');


Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

/**
 * Auth Routes
 */
Route::group(['middleware' => ['auth']], function () {

    Route::get('user/{urlSegment?}', 'UserController@profile');

    Route::delete('bets/{usersBets}', 'BetsController@delete')->name('bets.delete');
    Route::post('bets/{usersBets}/accept', 'BetsController@accept')->name('bets.accept');

    Route::get('/game/{urlSegment}', 'GamesController@game')->name('game');
    Route::post('/game/{urlSegment}', 'BetsController@store');

    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
// Password Reset Routes...
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});