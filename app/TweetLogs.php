<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TweetLogs extends Model
{
    const MACHINE_LEARNING_FAILED = 1;
    const RETWEETED_ALREADY = 2;
    const GAME_NOT_IN_PROGRESS = 3;
    const NOT_A_VIDEO = 4;

    protected $guarded = ['id'];

    protected $softDelete = true;

    // Relations
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    public function game()
    {
        return $this->belongsTo(Games::class, 'game_id');
    }


    // Functions
    public function getTweetUrl()
    {
        return 'https://twitter.com/'.$this->team->twitter.'/status/'.$this->tweet_id;
    }
}
