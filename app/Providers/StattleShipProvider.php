<?php

namespace App\Providers;

use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\ServiceProvider;
use Cache;
use App\Teams;
use App\Leagues;
use Thujohn\Twitter\Facades\Twitter;

class StattleShipProvider extends ServiceProvider
{

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.stattleship.com/',
            'headers' => [
                'Authorization'     => 'Token token='.env('STATTLESHIP_TOKEN'),
                'Content-Type'      => 'application/json',
                'Accept'            => 'application/vnd.stattleship.com; version=1'
            ],
        ]);
    }

    public function getGame($slug, $status = 'upcoming')
    {
        $team = Teams::where('slug', $slug)->firstOrFail();

        $res = $this->client->request('GET', $team->league->long_name.'/'.strtolower($team->league->name).'/games?status='.$status);
        $results = json_decode($res->getBody());

        $games = $this->mapGameData($results->games, $league);

        if(!$games){
            return '';
        }

        // Sort games by start date.
        usort($games, function($a, $b){
            return strtotime($a['start_date']) - strtotime($b['start_date']);
        });

        return $games;
    }

    /**
     * @param $league - one of four leagues.
     * @param $startDate
     * @return array
     */
    public function getGames(Leagues $league, $date = 'now')
    {
        $res = $this->client->request('GET', $league->long_name.'/'.$league->name.'/games?on='.$date);
        $results = json_decode($res->getBody());

        if(!$results->games){
            return;
        }

        $gameData = [];
        foreach($results->games as $game){
            $gameData[] = $this->mapGameData($game, $league);
        }

        return array_filter($gameData);
    }

    public function getTeams(Leagues $league)
    {
        $res = $this->client->request('GET', strtolower($league->long_name).'/'.strtolower($league->name).'/teams');
        $results = json_decode($res->getBody());

        foreach($results->teams as $team) {
            $teamData[] = $this->mapTeamData($team);
        }

        return $teamData;
    }


    /**
     * @param $games (array)
     * @param $league (str)
     * @return array|string
     */
    private function mapGameData($game, Leagues $league)
    {
        // Separate team names "Home vs Away"
        $teams = explode('vs', $game->title);

        // Find team id's by name and league.
        $homeTeam = Teams::where('nickname', trim($teams[1]))->where('league_id', $league->id)->first();
        $awayTeam = Teams::where('nickname', trim($teams[0]))->where('league_id', $league->id)->first();
        if(!$awayTeam || !$homeTeam) {
            return [];
        }

        return [
            'home_team_id'  => (int) $homeTeam->id,
            'away_team_id'  => (int) $awayTeam->id,
            'home_score'    => $game->status == 'upcoming' ? null : $game->home_team_score,
            'away_score'    => $game->status == 'upcoming' ? null : $game->away_team_score,
            'league_id'     => $league->id,
            'period'        => $game->period > 0 ? $game->period : NULL, // Returns 0 sometimes, show NULL instead
            'broadcast'     => $game->broadcast,
            'ended_at'      => $game->ended_at ? Carbon::parse($game->ended_at)->timezone('America/New_York')->format('Y-m-d H:i:s') : null,
            'start_date'    => Carbon::parse($game->started_at, $homeTeam->timezone)->timezone('America/New_York')->format('Y-m-d H:i:s')
        ];
    }


    private function mapTeamData($team)
    {
        echo $team->nickname." \n";
        $leagueName = explode("-", $team->slug)[0];
        $league = Leagues::where('name', $leagueName)->firstOrFail();

        $twitter = Twitter::getUsersSearch(['q' => $team->location.' '.$team->nickname]);

        return [
            'nickname' => $team->nickname,
            'hashtag' => $team->hashtag,
            'location' => $team->location,
            'latitude' => $team->latitude,
            'longitude' => $team->longitude,
            'league_id' => $league->id,
            'colors' => $team->colors,
            'slug' => $team->slug,
            'twitter' => $twitter[0]->screen_name
        ];
    }

}