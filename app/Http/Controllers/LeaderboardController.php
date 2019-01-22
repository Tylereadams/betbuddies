<?php

namespace App\Http\Controllers;

use App\UsersBets;
use App\User;
use App\Stats;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

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
