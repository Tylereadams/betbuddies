<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Players;
use Illuminate\Support\Facades\DB;

class PlayersController extends Controller
{
    //

    public function jsonSearch()
    {
        $q = Request::get('q');

        $playerQuery = Players::select('first_name', 'last_name', 'twitter', 'team_id')
            ->where(DB::raw("CONCAT(first_name,' ',last_name,' ',twitter)"), 'LIKE', '%'.$q.'%');

        if($q){
            $playerQuery->whereHas('tweets');
        }
        $players = $playerQuery->get();

        $players->load(['team.league', 'tweets']);

        // TODO: FIGURE OUT WHY CAMERON PAYNE ISN"T SHOWING UP
        $data = [];
        foreach($players as $player){
            $data[] = [
              'first_name' => $player->first_name,
              'last_name' => $player->last_name,
              'twitter' => $player->twitter,
              'html' => '<i class="fas fa-'.$player->team->league->long_name.'-ball"></i> '.$player->first_name.' '.$player->last_name.' <small>('.$player->twitter.')</small>'
            ];
        }

        return response()->json($data);
    }
}
