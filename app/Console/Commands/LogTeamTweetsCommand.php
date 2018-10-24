<?php

namespace App\Console\Commands;

use App\Services\TwitterHelper;
use App\Services\HighlightHelper;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Games;
use App\TweetLogs;

class LogTeamTweetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:log-tweets {date=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classifies tweets and logs them to tweet_logs table';

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
        // Get games within 10 hour range of date given
        $minDate = Carbon::parse($this->argument('date'))->subHours(3)->format('Y-m-d H:i:s');
        $maxDate = Carbon::parse($this->argument('date'))->addHours(3)->format('Y-m-d H:i:s');

        // Get games that have been played within 10 hours of the time given
        $games = Games::where('start_date', '>', $minDate)->where('start_date', '<', $maxDate)->get();
        $games->load(['homeTeam.tweets', 'awayTeam.tweets']);

        if(!$games){
            return 'No results.';
        }

        // Get the video's we've checked already
        $existingTweets = TweetLogs::where('created_at', '>', Carbon::now()->subHours(24))
            ->pluck('tweet_id')
            ->toArray();

        foreach($games as $game) {

            $teams = [$game->homeTeam, $game->awayTeam];

            // Loop through both teams in game
            foreach($teams as $team) {
                // Setup twitter credentials for each team's account
                $team->reconfigTeamTwitter();

                print $team->nickname."\n";

                // Get the off twitter team tweets
                $tweets = $team->getTimeline([$team->twitter]);

                // Loop through tweets
                foreach($tweets as $key=> $tweet){

                    // If we've already logged it OR tweet not during a game OR tweet is not a valid highlight
                    if(in_array($tweet->id, $existingTweets) || !TwitterHelper::isGameTweet($game, $tweet) || !HighlightHelper::isHighlight($tweet, $game->league)) {
                        continue;
                    }

                    print "Saving tweet... \n";

                    TweetLogs::updateOrCreate([
                        'tweet_id' => $tweet->id,
                        'media_url' => $tweet->extended_entities->media[0]->media_url,
                        'team_id' => $team->id,
                        'text' => $tweet->text,
                        'game_id' => $game->id
                    ]);

                    // Save the new tweet
                    $existingTweets[] = $tweet->id;
                }
            }
        }
    }
}
