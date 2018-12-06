<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\StreamableService;

class TweetLogs extends Model
{
    const MACHINE_LEARNING_FAILED = 1;
    const RETWEETED_ALREADY = 2;
    const GAME_NOT_IN_PROGRESS = 3;
    const NOT_A_VIDEO = 4;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::created(function($model){
            // Upload tweet to streamable, it auto grabs the video
            $streamable_code = $model->uploadToStreamable();
            $model->streamable_code = $streamable_code;

            $model->save();
        });
    }

    /***
     * Relations
     ***/
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    public function game()
    {
        return $this->belongsTo(Games::class, 'game_id');
    }

    public function players()
    {
        return $this->hasManyThrough(Players::class, PlayersTweets::class, 'tweet_logs_id', 'id', 'id', 'player_id');
    }


    /***
     * Functions
     ***/

    /**
     * Returns Twitter url for tweet
     * @return string
     */
    public function getTweetUrl()
    {
        return 'https://twitter.com/'.$this->team->twitter.'/status/'.$this->tweet_id;
    }

    public function getVideoPath()
    {
        $path = 'highlights/'.$this->game->league->name.'/'.$this->game->start_date->format('Y-m-d').'/'.str_replace(" ", "-", $this->game->homeTeam->nickname.' '.$this->game->awayTeam->nickname.' '.$this->game->start_date->format('Hi')).'/'.str_replace(" ", "-", $this->team->nickname.' '.$this->id).'.mp4';

        return $path;
    }

    /**
     * Uploads tweet video to Streamable and returns the streamable shortcode
     * @return mixed
     */
    public function uploadToStreamable()
    {
        $streamableService = new StreamableService($this->getTweetUrl());

        $response = json_decode($streamableService->uploadVideo());

        if(!isset($response->shortcode)){
            return;
        }

        return $response->shortcode;
    }
}
