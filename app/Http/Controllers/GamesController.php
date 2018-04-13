<?php

namespace App\Http\Controllers;

use App\Games;
use App\TeamsTweets;
use Carbon\Carbon;
use Thujohn\Twitter\Facades\Twitter;
use Illuminate\Support\Facades\Cache;

class GamesController extends Controller
{

    /**
     * Returns view for all games
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function games($date = 'now')
    {
        // Default date to today
        if(!$date){
            $date = \Request::get('date', 'today');
        }

        $date = Carbon::parse($date)->format('Y-m-d');
        $games = Games::where('start_date', 'LIKE', $date.'%')->get();

        $games->sortBy(function($game){
            return strtotime('now') - strtotime($game->start_date);
        });

        if($games->isEmpty()){
            return 'No games.';
        }

        $data = ['gamesByLeague' => null];
        // Add games to data
        foreach($games as $game) {
            $data['gamesByLeague'][$game->league->name][] = $game->getCardData();
        }

        $data['selectedLeague'] = \Request::get('league') ? \Request::get('league') : array_keys($data['gamesByLeague'])[0];
        $data['date'] = Carbon::parse($date)->format('M j, Y');
        $data['tomorrow'] = Carbon::parse($date)->addDay()->format('Y-m-d');
        $data['yesterday'] = Carbon::parse($date)->subDay()->format('Y-m-d');

        return view('games', $data);
    }

    /**
     * Show view for specific game
     * @param $urlSegment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function game($urlSegment)
    {
        $game = Games::where('url_segment', $urlSegment)->firstOrFail();

        $data['game'] = $game->getCardData();

        $data['bets'] = [];
        $bets = $game->bets->sortByDesc('created_at');

        foreach($bets as $bet){
            $data['bets'][] = $bet->getCardData();
        }

        $tweets = TeamsTweets::where('game_id', $game->id)
            ->orderBy('created_at', 'DESC')
            ->get();
        $tweets->load('team');

        foreach($tweets as $tweet){
            $data['tweetsToEmbed'][] = Cache::remember('embedded-tweets-'.$tweet->id, 120, function () use($tweet) {
                return Twitter::getOembed([
                    'url' => 'https://twitter.com/'.$tweet->team->twitter.'/status/'.$tweet->tweet_id,
                    'widget_type' => 'video'
                ]);
            });
        }

        return view('game', $data);
    }
}