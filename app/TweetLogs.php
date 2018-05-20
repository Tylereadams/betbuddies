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

    //
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
}
