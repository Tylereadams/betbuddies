<?php

namespace App\Http\Controllers;

use App\TeamsTweets;
use App\TweetLogs;
use Carbon\Carbon;
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
        $tweetLog->load(['team', 'players', 'game.awayTeam',  'game.homeTeam']);

        $tweets = [];
        foreach($tweetLog as $key => $tweet){
            $tweets[$key] = [
                'id' => $tweet->tweet_id,
                'isInvalid' => $tweet->is_invalid,
                'team' => [
                  'twitter' => $tweet->team->twitter,
                  'leagueId' => $tweet->team->league_id
                ],
                'imageUrl' => $tweet->media_url,
                'videoUrl' => $tweet->video_url,
                'text' => $tweet->text,
                'tweetUrl' => $tweet->getTweetUrl(),
                'mentions' => $tweet->players->map(function($player){
                    return [
                        'name' => $player->first_name.' '.$player->last_name,
                        'twitter' => $player->twitter
                    ];
                })->toArray()
            ];

            if(isset($tweet->game)){
                $tweets[$key]['game'] = [
                    'opponent' => $tweet->team->id == $tweet->game->awayTeam->id ? $tweet->game->homeTeam->nickname : $tweet->game->awayTeam->nickname,
                    'date' => Carbon::parse($tweet->game->start_date)->format('m/d')
                ];
            }
        }

        return view('admin.tweet-log', ['tweets' => $tweets, 'searchTerm' => $q]);
    }
}