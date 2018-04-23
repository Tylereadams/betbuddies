<?php

namespace App\Console\Commands;

use App\Games;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Providers\StattleShipProvider;
use App\Leagues;

class ImportGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:import-games {date=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates and inserts games.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(StattleShipProvider $statProvider)
    {

        $this->statProvider = $statProvider;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('date');

        // If it's 3am or earlier, get last night's games instead. Those could still be playing
        if($date == 'now' && date('H') < 3){
            $date = 'yesterday';
        }

        $leagues = Leagues::all();
        // Update each league's games
        foreach($leagues as $league){
            echo $league->name.": ";
            $games = $this->statProvider->getGames($league, $date);

            if(empty($games)) {
                echo "no games \n";
                continue;
            }

            // Update games
            $gamesUpdated = [];
            foreach($games as $game) {
                // Try to find existing game(s) on this day
                $existingGames = Games::where('start_date', 'LIKE', Carbon::parse($game['start_date'])->toDateString().'%')
                    ->where('home_team_id', $game['home_team_id'])
                    ->where('away_team_id', $game['away_team_id'])
                    ->get();

                if(count($existingGames)){
                    foreach($existingGames as $gameToUpdate) {
                        // Check if it's a doubleheader and make sure start dates are at least within 4 hours of each other
                        if(count($existingGames) > 1 && (strtotime($gameToUpdate->start_date) - strtotime($game['start_date'])) > (3600 * 4)){
                            continue;
                        }

                        // Update the game
                        $gameToUpdate->period = $game['period'];
                        $gameToUpdate->home_score = $game['home_score'];
                        $gameToUpdate->away_score = $game['away_score'];
                        $gameToUpdate->broadcast = $game['broadcast'];
                        $gameToUpdate->start_date = $game['start_date'];
                        $gameToUpdate->ended_at = $game['ended_at'];
                        $gamesUpdated[] = $gameToUpdate->save();
                    }
                } else {
                    // Create the new game
                    $newGame = new Games();
                    $newGame->home_team_id = $game['home_team_id'];
                    $newGame->away_team_id = $game['away_team_id'];
                    $newGame->period = $game['period'];
                    $newGame->league_id = $game['league_id'];
                    $newGame->home_score = $game['home_score'];
                    $newGame->away_score = $game['away_score'];
                    $newGame->broadcast = $game['broadcast'];
                    $newGame->start_date = $game['start_date'];
                    $newGame->ended_at = $game['ended_at'];

                    $gamesUpdated[] = $newGame->save();
                }
            }

            echo count($gamesUpdated)." checked \n";
        }
    }
}
