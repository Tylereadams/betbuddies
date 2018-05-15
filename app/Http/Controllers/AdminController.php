<?php

namespace App\Http\Controllers;

use App\TeamsTweets;

class AdminController extends Controller
{

    public function tweetLog()
    {
        $tweets = TeamsTweets::orderBy('created_at', 'DESC')->get();
        $tweets->load('team');

        return view('admin.tweet-log', ['tweets' => $tweets]);
    }
}