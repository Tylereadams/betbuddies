<?php

namespace App\Http\Controllers;

use App\Games;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Leagues;

class GamesController extends Controller
{

    /**
     * Returns view for all games
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function games()
    {
        // Default date to today
        $date = \Request::get('date', 'today');

        $date = Carbon::parse($date)->format('Y-m-d');
        $games = Games::where('start_date', 'LIKE', $date.'%')->orderBy('start_date', 'DESC')->get();

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

        return view('game', $data);
    }
}