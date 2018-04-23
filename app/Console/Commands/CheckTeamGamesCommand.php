<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Providers\StattleShipProvider;
use Carbon\Carbon;
use App\Games;

class CheckTeamGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:check-games {date=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks to make sure games on a given date for a given team are valid.';

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
        $games = Games::where('start_date', 'LIKE', Carbon::parse($date)->format('Y-m-d').'%')
            ->where('home_team_id', 110)->get();

        foreach($games as $game){
            $teamGames = $this->statProvider->getTeamGames($game->homeTeam);
            // TODO: Figure out if game is going to be played, playoffs sometimes don't if the series is over
            if(!$teamGames[0]['broadcast']){

            }
        }
    }
}
