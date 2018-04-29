<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UsersBets;
use App\User;

class UserController extends Controller
{
    //
    public function profile($urlSegment = null)
    {
        $user = $urlSegment ? User::where('url_segment', $urlSegment)->firstOrFail() : Auth::user();

        $data = [
            'user' => $user->getCardData(),
        ];

        $bets = UsersBets::where(function($q)use($user){
            $q->where('opponent_id', $user->id);
            $q->orWhere('user_id', $user->id);
        })->orderBy('created_at', 'DESC')->get();

        $bets->load(['game.homeTeam', 'game.awayTeam', 'user', 'opponent', 'opponentTeam']);

        $data['bets'] = [];

        $wins = 0;
        $losses = 0;
        $totalWinnings = 0;
        foreach($bets as $bet){
            $data['bets'][] = $bet->getCardData();

            $winner = $bet->getWinningUser();
            if(!$winner){
                continue;
            }

            if($winner->id  == $user->id){
                $wins++;
                $totalWinnings = $totalWinnings + $bet->amount;
            } else {
                $losses++;
                $totalWinnings = $totalWinnings - $bet->amount;
            }
        }

        $data['winnings'] = $totalWinnings;
        $data['betsWon'] = $wins;
        $data['betsLost'] = $losses;

        return view('profile', $data);
    }
}
