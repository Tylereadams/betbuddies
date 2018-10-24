<?php

namespace App\Http\Controllers;

use App\TeamsTweets;
use App\TweetLogs;

class AdminController extends Controller
{

    public function tweetLog()
    {
        $tweetLog = TweetLogs::orderBy('created_at', 'DESC')->take(100)->get();
        $tweetLog->load('team');

        foreach($tweetLog as $tweet){
            $tweets[] = [
                'id' => $tweet->tweet_id,
                'isInvalid' => $tweet->is_invalid,
                'team' => [
                  'twitter' => $tweet->team->twitter,
                  'leagueId' => $tweet->team->league_id
              ],
                'imageUrl' => $tweet->media_url,
                'text' => $tweet->text,
                'tweetUrl' => $tweet->getTweetUrl()
            ];
        }

        return view('admin.tweet-log', ['tweets' => $tweets]);
    }
}