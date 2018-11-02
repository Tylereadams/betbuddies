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

        if(!$q){
            return;
        }

        $players = Players::select('first_name', 'last_name', 'twitter', 'team_id')
            ->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'LIKE', '%'.$q.'%')
            ->withCount('tweets')
            ->orderBy('tweets_count', 'DESC')
            ->take(10)
            ->get();
        $players->load(['team.league', 'tweets']);

        $data = [];
        foreach($players as $player){

            $html = '<i class="fas '.$player->team->league->getIconName().'"></i> '.$player->first_name.' '.$player->last_name;
            $html .= $player->tweets_count ? ' <small>('.$player->tweets_count.')</small>' : '';

            $data[] = [
              'first_name' => $player->first_name,
              'last_name' => $player->last_name,
              'twitter' => $player->twitter,
              'tweet_count' => $player->tweets_count,
                // Auto complete HTML
              'html' => $html
            ];
        }

        return response()->json($data);
    }
}
