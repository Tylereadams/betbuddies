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
                $gamesUpdated[] = Games::updateOrCreate([
                    'home_team_id' => $game['home_team_id'],
                    'away_team_id' => $game['away_team_id'],
                    'start_date' => $game['start_date']
                ],[
                    'period' => $game['period'],
                    'league_id' => $game['league_id'],
                    'home_score' => $game['home_score'],
                    'away_score' => $game['away_score'],
                    'broadcast' => $game['broadcast'],
                    'ended_at' => $game['ended_at']
                ]);
            }
            echo count($gamesUpdated)." checked \n";
        }
    }
}
