<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UsersBets;
use App\User;
use App\Stats;

class UserController extends Controller
{
    //
    public function profile($urlSegment = null)
    {
        $user = $urlSegment ? User::where('url_segment', $urlSegment)->firstOrFail() : Auth::user();

        $data = [
            'user' => $user->getCardData(),
        ];

        $bets = UsersBets::where(function($q) use($user){
            $q->where('opponent_id', $user->id);
            $q->orWhere('user_id', $user->id);
        })->whereNotNull('opponent_id')
            ->orderBy('created_at', 'DESC')
            ->get();
        $bets->load(['game.homeTeam', 'game.awayTeam', 'user', 'opponent', 'opponentTeam']);

        $data['bets'] = [];
        foreach($bets as $bet){
            $data['bets'][] = $bet->getCardData();
        }

        $userStats = Stats::where('user_id', $user->id)->first();

        $data['winnings'] = $userStats ? $userStats->winnings : 0;
        $data['wins'] = $userStats ? $userStats->wins : 0;
        $data['losses'] = $userStats ? $userStats->losses : 0;
        $data['winPercentage'] = $userStats ? $userStats->win_percentage : 0.00;

        return view('profile', $data);
    }
}
