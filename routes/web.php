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


// Admin Routes
Route::get('tweet-log', 'AdminController@tweetLog');
Route::get('authorize-accounts', 'AdminController@authorizeAccounts');


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
});

Route::prefix('api')->group(function () {
    Route::get('games/{date?}', 'GamesController@gamesJson');
    Route::get('game/{urlSegment}', 'GamesController@gameJson');
});


// Twitter Routes
Route::get('twitter/login', ['as' => 'twitter.login', function(){
    $sign_in_twitter = true;
    $force_login = false;

    // Make sure we make this request w/o tokens, overwrite the default values in case of login.
    Twitter::reconfig(['token' => '', 'secret' => '']);
    $token = Twitter::getRequestToken(route('twitter.callback'));

    if (isset($token['oauth_token_secret']))
    {
        $url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);

        Session::put('oauth_state', 'start');
        Session::put('oauth_request_token', $token['oauth_token']);
        Session::put('oauth_request_token_secret', $token['oauth_token_secret']);

        return Redirect::to($url);
    }

    return Redirect::route('twitter.error');
}]);

Route::get('twitter/callback', ['as' => 'twitter.callback', function() {
    // You should set this route on your Twitter Application settings as the callback
    // https://apps.twitter.com/app/YOUR-APP-ID/settings
    if (Session::has('oauth_request_token'))
    {
        $request_token = [
            'token'  => Session::get('oauth_request_token'),
            'secret' => Session::get('oauth_request_token_secret'),
        ];

        Twitter::reconfig($request_token);

        $oauth_verifier = false;

        if (Input::has('oauth_verifier'))
        {
            $oauth_verifier = Input::get('oauth_verifier');
            // getAccessToken() will reset the token for you
            $token = Twitter::getAccessToken($oauth_verifier);
        }

        if (!isset($token['oauth_token_secret']))
        {
            return Redirect::route('twitter.error')->with('flash_error', 'We could not log you in on Twitter.');
        }

        $credentials = Twitter::getCredentials();

        if (is_object($credentials) && !isset($credentials->error))
        {
            // $credentials contains the Twitter user object with all the info about the user.
            // Add here your own user logic, store profiles, create new users on your tables...you name it!
            // Typically you'll want to store at least, user id, name and access tokens
            // if you want to be able to call the API on behalf of your users.

            // This is also the moment to log in your users if you're using Laravel's Auth class
            // Auth::login($user) should do the trick.

            Session::put('access_token', $token);

            return Redirect::to('/')->with('flash_notice', 'Congrats! You\'ve successfully signed in!');
        }

        return Redirect::route('twitter.error')->with('flash_error', 'Crab! Something went wrong while signing you up!');
    }
}]);

Route::get('twitter/error', ['as' => 'twitter.error', function(){
    // Something went wrong, add your own error handling here
}]);

Route::get('twitter/logout', ['as' => 'twitter.logout', function(){
    Session::forget('access_token');
    return Redirect::to('/')->with('flash_notice', 'You\'ve successfully logged out!');
}]);