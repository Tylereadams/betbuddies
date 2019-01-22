<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Carbon\Carbon;
use App\Stats;

class ImportStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:import-stats {minutes_ago}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the leaderboard table with new data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minutesAgo = $this->argument('minutes_ago');

        $users = User::whereHas('bets', function($q) use ($minutesAgo){
            if($minutesAgo) {
                $q->where('created_at', '>', Carbon::now()->subMinutes($minutesAgo));
            }
        })->get();
        $users->load('bets.winner', 'bets.loser', 'stats');

        foreach($users as $user) {

            if($user->stats){
                echo "found: ".$user->id."\n";
                $user->stats->delete();
            } else {
                echo "not found: ".$user->bets->first()."\n";
            }

            foreach($user->bets as $bet){
                if(!$bet->winner || !$bet->loser) {
                    continue;
                }

                // Update winner
                $winnerStats = Stats::firstOrNew([
                    'user_id' => $bet->winner->id
                ]);

                $winnerStats->wins++;
                $winnerStats->winnings = $winnerStats->winnings + $bet->amount;
                $winnerStats->win_percentage = $winnerStats->getWinPercentage();
                $winnerStats->save();

                // Update loser
                $loserStats = Stats::firstOrNew([
                    'user_id' => $bet->loser->id
                ]);
                $loserStats->losses++;
                $loserStats->winnings = $loserStats->winnings - $bet->amount;
                $loserStats->win_percentage = $loserStats->getWinPercentage();
                $loserStats->save();
            }
        }

    }
}
