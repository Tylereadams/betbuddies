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
        $tweetLogQuery = TweetLogs::whereNotNull('downloaded');

        // Search for player's highlight tweets
        if(Request::has('q')){
            $tweetLogQuery->whereHas('players', function($query) use ($q){
                $query->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'LIKE', '%'.$q.'%');
            });
        }

        $tweetPaginator = $tweetLogQuery->orderBy('created_at', 'DESC')
            ->paginate(15);
        $tweetPaginator->load(['team', 'players.tweets', 'game.awayTeam',  'game.homeTeam']);

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
                'mentions' => $tweet->players->map(function($player){
                    return [
                        'id' => $player->id,
                        'name' => $player->first_name.' '.$player->last_name,
                        'twitter' => $player->twitter,
                        'tweetCount' => $player->tweets->count()
                    ];
                })->toArray()
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

        $topRelatedPlayers = collect($topRelatedPlayers)->unique()->sortByDesc(function($player){
            return $player['tweetCount'];
        })->take(5);

        return view('admin.tweet-log', [
            'paginator' => $tweetPaginator,
            'tweets' => $tweets,
            'topRelatedPlayers' => $topRelatedPlayers,
            'searchTerm' => $q
        ]);
    }
}