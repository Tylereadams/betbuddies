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

        $players = Players::select('first_name', 'last_name', 'twitter')
            ->where(DB::raw("CONCAT(first_name,' ',last_name,' ',twitter)"), 'LIKE', '%'.$q.'%')
            ->get();

        $data = [];
        foreach($players as $player){
            $data[] = [
              'first_name' => $player->first_name,
              'last_name' => $player->last_name,
              'twitter' => $player->twitter,
              'html' => '<i class="fas fa-football-ball"></i> '.$player->first_name.' '.$player->last_name.' <small>('.$player->twitter.')</small>'
            ];
        }

        return response()->json($data);
    }
}
