<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Providers\StattleShipProvider;
use App\Teams;
use Carbon\Carbon;
use App\Leagues;
use Thujohn\Twitter\Facades\Twitter;

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
    protected $description = 'Imports offical teams twitter profile image and banner and updates profile/banner.';

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
        $leagues = Leagues::all();

        foreach($leagues as $league){
            $teams = Teams::where('league_id', $league->id)->whereHas('credentials', function($q){
                $q->whereNotNull('token');
                $q->whereNotNull('token_secret');
            })->get();

            foreach($teams as $team) {
                echo $team->nickname."...\n";

                // Get the config for this team's twitter account
                Twitter::reconfig(['token' => $team->credentials->token, 'secret' => decrypt($team->credentials->token_secret)]);

                $twitterAccount = Twitter::getUsers(['screen_name' => $team->twitter]);

                Twitter::postUserBanner([
                    'banner' => base64_encode(file_get_contents($twitterAccount->profile_banner_url))
                ]);

                Twitter::postProfileImage([
                    'image' => base64_encode(file_get_contents('https://twitter.com/'.$team->twitter.'/profile_image?size=original'))
                ]);
            }
        }
    }
}
