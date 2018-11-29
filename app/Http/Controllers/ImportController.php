<?php

namespace App\Http\Controllers;

use App\Services\RedditHelper;
use GuzzleHttp;
use Auth;
use App\Providers\StattleShipProvider;
use App\Teams;
use App\TeamsColors;
use App\Leagues;


class ImportController extends Controller
{

    protected $importer;

    public function __construct(StattleShipProvider $statProvider)
    {
        $this->importer = $statProvider;
    }

    /**
     * Imports teams if we don't have them already.
     * @return mixed
     */
    public function importTeams()
    {
        $leagues = Leagues::where('name', 'nba')->get();

        foreach($leagues as $league){
            $teams = $this->importer->teams($league->name);

            foreach($teams as $team){

                $colors = $team['colors'];
                unset($team['colors']);
                $team = Teams::firstOrCreate($team);

                if($colors){
                    foreach($colors as $color){
                        TeamsColors::firstOrCreate(['team_id' => $team->id, 'hex' => $color]);
                    }
                }
            }
        }
    }

    public function findHighlights()
    {
        // Find most recent highlight game threads
        $highlightsThreadQuery = 'https://www.reddit.com/r/nfl/search.json?q=picture%20gif%20video%20highlights%20thread&restrict_sr=1&t=all&sort=new';
        $json = json_decode(file_get_contents($highlightsThreadQuery));

        // Get thread url
        $highlightThreadUrl = $json->data->children[0]->data->url.".json";

        $redditHelper = new RedditHelper($highlightThreadUrl);

        $links = $redditHelper->getThreadLinks();
        dd($links);
    }
}