<?php

namespace App\Http\Controllers;

use App\Stats;
use App\Games;

class LeaderboardController extends Controller
{
    //
    public function index()
    {
        $stats = Stats::orderBy('win_percentage', 'DESC')
            ->orderBy('winnings', 'DESC')
            ->get();
        $stats->load('user');

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
