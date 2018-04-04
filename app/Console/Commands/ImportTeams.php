<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Providers\StattleShipProvider;
use App\Teams;
use Carbon\Carbon;
use App\Leagues;

class ImportTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:import-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports teams or updates them if already present.';

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
        $leagues = Leagues::all();

        foreach($leagues as $league){
            $teams = $this->statProvider->getTeams($league);

            echo "\n\n\nUpdating teams... \n";

            foreach($teams as $team) {
                $oldTeam = Teams::where('location', $team['location'])
                    ->where('nickname', $team['nickname'])
                    ->first();

                if(!$oldTeam) {
                    echo $team['nickname']."... not found \n";
                    continue;
                }

                $oldTeam->league_id = $team['league_id'];
                $oldTeam->twitter = $team['twitter'];
                $oldTeam->slug = $team['slug'];
                $oldTeam->hashtag = $team['hashtag'];
                $oldTeam->location = $team['location'];
                $oldTeam->latitude = $team['latitude'];
                $oldTeam->longitude = $team['longitude'];
                $oldTeam->updated_at = Carbon::now();

                $oldTeam->save();
                echo "\n";
            }
        }
    }
}
