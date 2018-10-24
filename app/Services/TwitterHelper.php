<?php

namespace App\Services;

use App\Games;
use Carbon\Carbon;

class TwitterHelper
{

    const BUFFER_MINUTES = 20;

    public function __construct()
    {

    }

    public static function isGameTweet(Games $game, $tweet)
    {
        $timeOfTweet = Carbon::parse($tweet->created_at);
        $gameStartDate = Carbon::parse($game->start_date);
        // Add 20 minute buffer to end time since vids will take at least that to be posted.
        $gameEndDate = $game->ended_at ? Carbon::parse($game->ended_at)->addMinutes(Self::BUFFER_MINUTES) : Carbon::parse('now');

        $gameHasStarted = ($timeOfTweet > $gameStartDate && $timeOfTweet < $gameEndDate);

        if(!$gameHasStarted || $tweet->retweeted) {
            return false;
        }

        return true;
    }

}