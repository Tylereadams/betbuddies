<?php

namespace App\Http\Controllers;

use App\Games;
use App\Leagues;
use App\Services\CardCreator;
use App\TweetLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Auth;
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
//        $game = Games::where('url_segment', $urlSegment)->firstOrFail();
//        $game->load(['bets.user', 'bets.opponent', 'bets.opponentTeam', 'bets.game.homeTeam', 'bets.game.awayTeam']);
//
//        $data['game'] = $game->getCardData();
//        $data['bets'] = $game->bets->sortByDesc('created_at');
//
//        $tweets = TweetLogs::where('game_id', $game->id)
//            ->orderBy('created_at', 'ASC')
//            ->get();
//        $tweets->load('team.players');
//
//        $data['tweets'] = [];
//        foreach($tweets as $tweet){
//            $data['tweets'][] = [
//                'imageUrl' => $tweet->media_url,
//                'highlightUrl' => $tweet->highlightUrl(),
//                'players' => $tweet->players->map(function($player){
//                    return [
//                        'name' => $player->first_name.' '.$player->last_name
//                    ];
//                }),
//                'period' => $tweet->period
//            ];
//        }
//
//        $data['venue']['photoUrl'] = $game->homeTeam->venue ? $game->homeTeam->venue->photoUrl() : '';

        return view('game', ['urlSegment' => $urlSegment]);
    }

    public function gameJson($urlSegment)
    {
        $game = Games::where('url_segment', $urlSegment)->first();
        $game->load([
            'bets.user',
            'bets.game.homeTeam',
            'bets.game.awayTeam',
            'bets' => function ($q) {
            $q->orderBy('created_at', 'DESC');
        }]);

        $data['game'] = $game->getCardData();

        $data['authCheck'] = Auth::check();

        $data['highlights'] = $game->tweets->map(function($tweet){
           return $tweet->getCardData();
        });
        $data['venueThumbUrl'] = $game->homeTeam->venue ? $game->homeTeam->venue->photoUrl() : '';

        return response()->json($data);
    }

    public function gamesJson($date = 'now')
    {
        // Default date to today
        if(!$date){
            $date = \Request::get('date', 'today');
        }

        $date = Carbon::parse($date)->format('Y-m-d');

        $data['gamesByLeague'] = $this->getGamesData($date);

        return response()->json($data);
    }

    private function getGamesData($date = 'now')
    {
        $startDate = Carbon::parse($date)->subDay(1)->format('Y-m-d');
        $endDate = Carbon::parse($date)->addDay(5)->format('Y-m-d');

        $games = Cache::remember('games-data-'.$startDate.'-'.$endDate, 2, function () use ($startDate, $endDate) {
            return Games::where('start_date', '>', $startDate)
                ->where('start_date', '<', $endDate)
                ->orderByRaw('FIELD(league_id,'.Leagues::NFL_ID.','.Leagues::NBA_ID.','.Leagues::MLB_ID.','.Leagues::NHL_ID.')')
                ->orderByRaw('FIELD(status,'.Games::IN_PROGRESS.','.Games::ENDED.','.Games::UPCOMING.','.Games::POSTPONED.')')
                ->orderBy('start_date')
                ->get();
        });
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