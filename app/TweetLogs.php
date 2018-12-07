<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
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
            // Download the video from the tweet if there's a url
            if($model->video_url){
                $model->downloadVideo();
            } else { // No video url to download, get the streamable code to download from later
                $streamable = new StreamableService($model->getTweetUrl());
                $streamableCode = $streamable->uploadVideo();
                $response = json_decode($streamableCode);

                $model->streamable_code = $response->shortcode;
                $model->save();
            }
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


    /******************
     * Functions
     ******************/

    /**
     * Returns Twitter url for tweet
     * @return string
     */
    public function getTweetUrl()
    {
        return 'https://twitter.com/'.$this->team->twitter.'/status/'.$this->tweet_id;
    }

    /**
     * Returns path of highlight video within Digital Ocean
     * @return string
     */
    public function getVideoPath()
    {
        $leagueName = $this->game->league->name;
        $startDate = $this->game->start_date->format('Y-m-d');
        $gameSlug = str_slug($this->game->homeTeam->nickname . ' ' . $this->game->awayTeam->nickname . ' ' . $this->game->id);
        $fileName = str_slug($this->team->nickname . ' ' . $this->id);
        $extension = 'mp4';

        $path = 'highlights/' .$leagueName . '/' . $startDate . '/' . $gameSlug . '/' . $fileName . '.' . $extension;

        return $path;
    }

    public function downloadVideo()
    {
        // create curl resource
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->video_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $output = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);

        // Save to path on Digital Ocean
        $filePath = $this->getVideoPath();
        $response = Storage::disk('ocean')->put($filePath, $output, 'public');

        // Mark as downloaded
        $this->downloaded = $response;
        $this->save();
    }
}
