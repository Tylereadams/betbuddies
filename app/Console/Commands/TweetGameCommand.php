<?php

namespace App\Console\Commands;

use App\Leagues;
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
    protected $signature = 'betbuddies:tweet-highlights {date=now}';

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
        // Get games within 10 hour range of date given
        $minDate = Carbon::parse($this->argument('date'))->subHours(5)->format('Y-m-d H:i:s');
        $maxDate = Carbon::parse($this->argument('date'))->addHours(5)->format('Y-m-d H:i:s');

        // Get games that have been played within 10 hours of the time given
        $games = Games::where('start_date', '>', $minDate)
            ->where('start_date', '<', $maxDate)
            // TODO: Only MLB games for now, no data for the others.
            ->where('league_id', Leagues::MLB_ID)
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
