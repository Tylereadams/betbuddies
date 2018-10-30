<?php

namespace App\Console\Commands;

use App\PlayersTweets;
use App\TweetLogs;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Players;

class MatchTweetsToPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:match-tweets-to-players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates tweet_players table and matches players to logged tweets';

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
        // Get all tweets containing text from last 30 days
        $allTweets = TweetLogs::whereNotNull('text')->where('created_at', '>', Carbon::now()->subDays(30))->get();
        $allTweets->load('team.players');

        foreach($allTweets as $tweet){
            // Go through list of players on team to find mention of their name or twitter handle
            foreach($tweet->team->players as $player){

                // Create a string of searchable names to explode. Some twitter handles are missing so this will remove them from the array
                $searchableNameString = $player->first_name.','.$player->last_name.','.$player->twitter;

                if(str_contains(strtolower($tweet->text), explode(',', strtolower($searchableNameString)))){
                    // Insert into players tweets table
                    PlayersTweets::updateOrCreate([
                        'player_id' => $player->id,
                        'tweet_logs_id' => $tweet->id
                    ]);
                }
            }
        }
    }
}
