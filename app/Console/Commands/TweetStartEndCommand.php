<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Thujohn\Twitter\Facades\Twitter;
use Carbon\Carbon;
use App\Games;
use Illuminate\Support\Facades\Cache;

class TweetStartEndCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:tweet-start-end {date=now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweets the beginning and end of a game.';

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
            ->get();
//        $games->load(['homeTeam.tweets', 'awayTeam.tweets']);

        if(!$games){
            return 'No results.';
        }

        foreach($games as $game) {
            $startDate = Carbon::parse($game->start_date);

            // Game hasn't started yet, keep it movin'
            if(!$startDate->isPast()){
                continue;
            }

            // Send end tweet 15 minutes after game has ended
            if(Carbon::parse($game->ended_at)->addMinute(15)->isPast()){
                // Only send the ending tweet once, didn't want to save these tweets to DB so storing in cache for 10 hours if it got sent
                Cache::remember('ending-tweet-'.$game->id, 60 * 10, function ()use($game) {
                    $game->homeTeam->sendEndTweet($game);
                    $game->awayTeam->sendEndTweet($game);
                    return true;
                });
            }
        }
    }
}
