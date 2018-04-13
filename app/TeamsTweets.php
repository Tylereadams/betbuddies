<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamsTweets extends Model
{
    //
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $fillable = ['team_id', 'game_id', 'tweet_id', 'media_url'];


    /**
     * Relations
     */
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }


    /**
     * Functions
     */

}
