<?php

namespace App\Http\Controllers;

use App\Stats;

class LeaderboardController extends Controller
{
    //
    public function index()
    {
        $stats = Stats::orderBy('win_percentage', 'DESC')
            ->orderBy('winnings', 'DESC')
            ->get();
        $stats->load('user');

        $games = Games::take(1000)->orderBy('created_at', 'DESC')->get();

        foreach($games as $game) {
            // Update game's bets
            $game->updateBets();
        }

        $statsData = [];
        foreach($stats as $stat) {
            $statsData[] = [
                'name' => $stat->user->name,
                'wins' => $stat->wins,
                'losses' => $stat->losses,
                'win_percentage' => $stat->win_percentage,
                'winnings' => $stat->winnings,
                'profileUrl' => $stat->user->profileUrl()
            ];
        }

        return view('leaderboard', ['stats' => $statsData]);
    }
}
