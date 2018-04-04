<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp;
use App\Players;
use Thujohn\Twitter\Facades\Twitter;

class StattleshipApi extends Model
{
    //
    public function __construct()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.stattleship.com/',
            'headers' => [
                'Authorization'     => 'Token token='.env('STATTLESHIP_TOKEN'),
                'Content-Type'      => 'application/json',
                'Accept'            => 'application/vnd.stattleship.com; version=1'
            ]
        ]);
    }

    public function getPlayers(Teams $team)
    {
        $res = $this->client->request('GET', 'basketball/nba/teams');
        $results = json_decode($res->getBody());

        foreach($results->teams as $apiTeam) {
            if($apiTeam->location == 'Cleveland'){
                $result = json_decode($this->client->request('GET', 'basketball/nba/players?team_id='.$apiTeam->slug)->getBody());
            }
        }

        // Get the config for this team's twitter account
        Twitter::reconfig([
            'consumer_key' => env('TWITTER_CONSUMER_KEY'.$team->getKey()),
            'consumer_secret' => env('TWITTER_CONSUMER_SECRET'.$team->getKey()),
            'token' => env('TWITTER_ACCESS_TOKEN'.$team->getKey()),
            'secret' => env('TWITTER_ACCESS_TOKEN_SECRET'.$team->getKey())
        ]);

        // Only get the top 10 most recent players.
        $players = collect($result->players)->sortByDesc('updated_at')->take(10);
        $playersCreated = [];
        foreach($players as $player) {

            $twitterResult = Twitter::getUsersSearch(['q' => $player->first_name.' '.$player->last_name, 'count' => 1]);

            $playersCreated[] = Players::firstOrCreate([
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                'team_id' => $team->id,
            ],[
                'twitter' => $twitterResult[0]->screen_name
            ]);

        }

        return $playersCreated;
    }
}
