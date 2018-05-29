<?php

namespace App\Http\Controllers;

use App\TeamsTweets;
use App\TweetLogs;

class AdminController extends Controller
{

    public function tweetLog()
    {
        $tweetLog = TweetLogs::orderBy('created_at', 'DESC')->get();
        $tweetLog->load('team');

        foreach($tweetLog as $tweet){
            $tweets[] = [
                'id' => $tweet->tweet_id,
                'isInvalid' => $tweet->is_invalid,
                'team' => [
                  'twitter' => $tweet->team->twitter,
                  'leagueId' => $tweet->team->league_id
              ],
            ];
        }

        return view('admin.tweet-log', ['tweets' => $tweets]);
    }
}