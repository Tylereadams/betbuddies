<?php

namespace App\Http\Controllers;

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

        // TODO: Filter out bets that aren't acceptable and haven't been accepted
        $bets = UsersBets::where(function($q) use($user){
            $q->where('opponent_id', $user->id);
            $q->orWhere('user_id', $user->id);
        })->whereNotNull('winning_user_id')
            ->whereNotNull('losing_user_id')
            ->orderBy('created_at', 'DESC')
            ->get();
        $bets->load(['game.homeTeam', 'game.awayTeam', 'user', 'opponent', 'opponentTeam']);

        $data['bets'] = [];
        foreach($bets as $bet){
            $data['bets'][] = $bet->getCardData();
        }

        $betsWon = $bets->where('winning_user_id', $user->id);
        $betsLost = $bets->where('losing_user_id', $user->id);
        $totalBets = $betsWon->count() + $betsLost->count();

        $data['winnings'] = $betsWon->sum('amount') - $betsLost->sum('amount');
        $data['wins'] = $betsWon->count();
        $data['losses'] = $betsLost->count();
        $data['winPercentage'] = $betsWon->count() ? $betsWon->count() / $totalBets : 0;

        return view('profile', $data);
    }
}
