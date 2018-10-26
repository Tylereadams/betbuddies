<?php

namespace App\Http\Controllers;

use App\TeamsTweets;
use App\TweetLogs;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    public function tweetLog()
    {
        $q = Request::get('q');

        $tweetLogQuery = TweetLogs::whereNotNull('text');

        // Search for player's highlight tweets
        if(Request::has('q')){
            $tweetLogQuery->whereHas('players', function($query) use ($q){
                $query->where(DB::raw("CONCAT(first_name,' ',last_name,' ',twitter)"), 'LIKE', '%'.$q.'%');
            });
        }

        $tweetLog = $tweetLogQuery->orderBy('created_at', 'DESC')
            ->take(54)
            ->get();
        $tweetLog->load(['team', 'players']);

        $tweets = [];
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
                'tweetUrl' => $tweet->getTweetUrl(),
                'mentions' => $tweet->players->map(function($player){
                    return [
                        'name' => $player->first_name.' '.$player->last_name,
                        'twitter' => $player->twitter
                    ];
                })->toArray()
            ];
        }

        return view('admin.tweet-log', ['tweets' => $tweets, 'searchTerm' => $q]);
    }
}