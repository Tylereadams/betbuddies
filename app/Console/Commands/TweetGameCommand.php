<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Games;

class TweetGameCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:tweet-games {date=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends tweets for today\'s games';

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
        $date = Carbon::parse($this->argument('date'));

        // Add buffer time for game so that we get games past midnight
        $minDate = $date->subHours(6)->format('Y-m-d H:i:s');
        $maxDate = $date->addHours(4)->format('Y-m-d H:i:s');

        // Get games that have been played within 10 hours of the time given
        $games = Games::where('start_date', '>', $minDate)
            ->where('start_date', '<', $maxDate)
            ->get();
        $games->load(['homeTeam.tweets', 'awayTeam.tweets']);

        if(!$games){
            return 'No results.';
        }

        $result = [];
        foreach($games as $game) {
            $teams = [$game->homeTeam, $game->awayTeam];
            foreach($teams as $team) {
                $result[] = $team->sendGameTweets($game);
            }
        }
    }
}
