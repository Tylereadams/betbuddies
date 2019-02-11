<?php

namespace App\Console\Commands;

use App\Leagues;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Teams;

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

        // Get teams with games within date and has credentials
        $teams = Teams::where(function($q) use($minDate, $maxDate) {
            $q->whereHas('awayGames', function($q) use($minDate, $maxDate) {
                $q->where('start_date', '>', $minDate);
                $q->where('start_date', '<', $maxDate);
            });
            $q->orWhereHas('homeGames', function($q) use($minDate, $maxDate) {
                $q->where('start_date', '>', $minDate);
                $q->where('start_date', '<', $maxDate);
            });
        })->whereHas('credentials')->get();

        if(!$teams){
            return 'No team twitters with games.';
        }

        $result = [];
        foreach($teams as $team) {
            $result[] = $team->sendTweets();
        }
    }
}
