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
            //$teams = $this->statProvider->getTeams($league);
            $teams = Teams::where('league_id', $league->id)->get();

            echo "\n\n\nUpdating teams... \n";

            foreach($teams as $team) {
                $teamToUpdate = Teams::where('location', $team['location'])
                    ->where('nickname', $team['nickname'])
                    ->first();

                if(!$teamToUpdate) {
                    echo $team['nickname']."... not found \n";
                    continue;
                }

                // Swap the twitter keys so we can use it on the team
                if(getenv('TWITTER_CONSUMER_KEY'.$teamToUpdate->getKey())){
                    // Get the config for this team's twitter account
                    Twitter::reconfig([
                        'consumer_key' => env('TWITTER_CONSUMER_KEY'.$teamToUpdate->getKey()),
                        'consumer_secret' => env('TWITTER_CONSUMER_SECRET'.$teamToUpdate->getKey()),
                        'token' => env('TWITTER_ACCESS_TOKEN'.$teamToUpdate->getKey()),
                        'secret' => env('TWITTER_ACCESS_TOKEN_SECRET'.$teamToUpdate->getKey())
                    ]);

                    $twitterAccount = Twitter::getUsers(['screen_name' => $team->twitter]);

                    Twitter::postUserBanner([
                        'banner' => base64_encode(file_get_contents($twitterAccount->profile_banner_url))
                    ]);

                    Twitter::postProfileImage([
                        'image' => base64_encode(file_get_contents('https://twitter.com/'.$team->twitter.'/profile_image?size=original'))
                    ]);
                }

                $teamToUpdate->league_id = $team['league_id'];
                $teamToUpdate->twitter = $team['twitter'];
                $teamToUpdate->slug = $team['slug'];
                $teamToUpdate->hashtag = $team['hashtag'];
                $teamToUpdate->location = $team['location'];
                $teamToUpdate->latitude = $team['latitude'];
                $teamToUpdate->longitude = $team['longitude'];
                $teamToUpdate->updated_at = Carbon::now();

                $teamToUpdate->save();
                echo "\n";
            }
        }
    }
}
