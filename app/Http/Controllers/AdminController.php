<?php

namespace App\Http\Controllers;

use App\Players;
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
        $tweetLogQuery = TweetLogs::whereNotNull('downloaded');

        // Search for player's highlight tweets
        if(Request::has('q')){

            $playerId = Players::where(DB::raw("CONCAT(first_name,' ',last_name)"),  $q)->first()->pluck('id');

            if(!$playerId){
                abort(404);
            }

            $tweetLogQuery->whereHas('players', function($query) use ($playerId){
                $query->where('id', $playerId);
            });
        }

        $tweetPaginator = $tweetLogQuery->orderBy('created_at', 'DESC')->paginate(15);
        $tweetPaginator->load(['team', 'players.tweets', 'game.awayTeam',  'game.homeTeam', 'game.league']);

        $tweets = [];
        $topRelatedPlayers = [];
        foreach($tweetPaginator as $key => $tweet){
            $tweets[$key] = [
                'id' => $tweet->tweet_id,
                'isInvalid' => $tweet->is_invalid,
                'team' => [
                  'twitter' => $tweet->team->twitter,
                  'leagueId' => $tweet->team->league_id
                ],
                'imageUrl' => $tweet->media_url,
                'text' => $tweet->text,
                'highlightUrl' => $tweet->highlightUrl(),
                'players' => $tweet->players->map(function($player){
                    return [
                        'name' => $player->first_name.' '.$player->last_name
                    ];
                }),
                'period' => $tweet->period
            ];

            if(isset($tweets[$key]['mentions'][0])){
                $topRelatedPlayers[] = $tweets[$key]['mentions'][0];
            }

            if(isset($tweet->game)){
                $tweets[$key]['game'] = [
                    'opponent' => $tweet->team->id == $tweet->game->awayTeam->id ? $tweet->game->homeTeam->nickname : $tweet->game->awayTeam->nickname,
                    'date' => Carbon::parse($tweet->game->start_date)->format('m/d')
                ];
            }
        }

        $topRelatedPlayers = collect($topRelatedPlayers)->unique()->take(5)->sortByDesc(function($player){
            return $player['tweetCount'];
        });

        return view('admin.tweet-log', [
            'paginator' => $tweetPaginator,
            'tweets' => $tweets,
            'topRelatedPlayers' => $topRelatedPlayers,
            'searchTerm' => $q
        ]);
    }
}