<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UsersBets;

class UserController extends Controller
{
    //
    public function profile()
    {
        $user = Auth::user();
        $data = [
            'user' => $user->getCardData(),
        ];

        $bets = UsersBets::where(function($q){
            $q->where('opponent_id', Auth::id());
            $q->orWhere('user_id', Auth::id());
        })->orderBy('created_at', 'DESC')->get();

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
