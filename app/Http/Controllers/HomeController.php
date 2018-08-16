<?php

namespace App\Http\Controllers;

use App\UsersBets;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bets = UsersBets::latest()->take(10)->get();
        $bets->load(['game.homeTeam', 'game.awayTeam', 'user', 'opponent', 'opponentTeam']);

        $data['bets'] = [];

        foreach($bets as $bet) {
            $data['bets'][] = $bet->getCardData();
        }

        return view('home', $data);
    }
}
