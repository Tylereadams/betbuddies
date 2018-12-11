<?php

namespace App\Http\Controllers;

use App\Games;
use App\Services\CardCreator;
use App\TweetLogs;
use Carbon\Carbon;
//use Intervention\Image\ImageManager;
//use Thujohn\Twitter\Facades\Twitter;
//use Illuminate\Support\Facades\Cache;
//use Intervention\Image\Facades\Image;
//use Illuminate\Support\Facades\File;

class GamesController extends Controller
{

    /**
     * Returns view for all games
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function gamesByDate($date = 'now')
    {
        $leagueName = \Request::get('league');
        $date = Carbon::parse($date)->format('Y-m-d');

        $data['urlDate'] = $date;
        $data['date'] = Carbon::parse($date)->format('M j, Y');
        $data['tomorrow'] = Carbon::parse($date)->addDay()->format('Y-m-d');
        $data['yesterday'] = Carbon::parse($date)->subDay()->format('Y-m-d');
        $data['gamesByLeague'] = $this->getGamesData($date);
        $data['selectedLeague'] = $leagueName;

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
        $game->load(['bets.user', 'bets.opponent', 'bets.opponentTeam', 'bets.game.homeTeam', 'bets.game.awayTeam']);

        $data['game'] = $game->getCardData();

        $data['bets'] = [];
        $bets = $game->bets->sortByDesc('created_at');

        foreach($bets as $bet){
            $data['bets'][] = $bet->getCardData();
        }

        $tweets = TweetLogs::where('game_id', $game->id)
            ->orderBy('created_at', 'ASC')
            ->get();
        $tweets->load('team');

        $data['tweets'] = [];
        foreach($tweets as $tweet){
            $data['tweets'][] = [
                'imageUrl' => $tweet->media_url,
                'highlightUrl' => $tweet->highlightUrl(),
                'players' => $tweet->players->map(function($player){
                    return [
                        'name' => $player->first_name.' '.$player->last_name
                    ];
                })
            ];
        }

        $data['venue']['photoUrl'] = $game->homeTeam->venue ? $game->homeTeam->venue->photoUrl() : '';

        return view('game', $data);
    }

    public function gamesJson($date = 'now')
    {
        // Default date to today
        if(!$date){
            $date = \Request::get('date', 'today');
        }

        $date = Carbon::parse($date)->format('Y-m-d');

        $games = Games::where('start_date', 'LIKE', $date.'%')
            ->orderBy('league_id')
            ->orderBy('status')
            ->orderBy('period')
            ->orderBy('start_date')
            ->get();
        $games->load(['homeTeam', 'awayTeam', 'league']);

        $data['gamesByLeague'] = $this->getGamesData($date);

        return response()->json($data);
    }

    private function getGamesData($date = 'now')
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        $games = Games::where('start_date', 'LIKE', $date.'%')
            ->orderBy('league_id')
            ->orderBy('status')
            ->orderBy('period')
            ->orderBy('start_date')
            ->get();
        $games->load(['homeTeam', 'awayTeam', 'league']);

        $data = [];
        // Add games to data
        foreach($games as $game) {
            $data[$game->league->name][] = $game->getCardData();
        }

        return $data;
    }

    /**
     * Returns an image of the final score and stadium background
     * @return mixed
     */
//    public function image()
//    {
//        $game = Games::find(\Request::get('id'));
//
//        if(!$game){
//            $game = Games::latest()->first();
//        }
//
//        $cardCreator = new CardCreator($game);
//
//        $img = $cardCreator->getGameCard();
//
//        return $img->response('png');
//    }
}