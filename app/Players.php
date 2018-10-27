<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Players extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];


    // Relations
    public function team()
    {
        return $this->belongsTo(Teams::class);
    }

    public function tweets()
    {
        return $this->hasManyThrough(TweetLogs::class, PlayersTweets::class, 'player_id', 'id', 'id', 'tweet_logs_id');
    }
}
