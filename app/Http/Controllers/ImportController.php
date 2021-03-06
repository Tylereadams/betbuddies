<?php

namespace App\Http\Controllers;

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



}