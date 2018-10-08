<?php

namespace App;

use App\Services\CardCreator;
use Illuminate\Database\Eloquent\Model;
use Thujohn\Twitter\Facades\Twitter;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Cache;

class Teams extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['nickname', 'location', 'latitude', 'longitude', 'hashtag', 'league'];


    /**
     * Relations
     */
    public function league()
    {
        return $this->belongsTo(Leagues::class);
    }

    public function colors()
    {
        return $this->hasMany(TeamsColors::class, 'team_id');
    }

    public function tweets()
    {
        return $this->hasMany(TeamsTweets::class, 'team_id');
    }

    public function venue()
    {
        return $this->hasOne(Venues::class, 'team_id');
    }


    /**
     * Functions
     */
    public function logoUrl()
    {
        return asset('img/logos/'.str_slug($this->nickname.' '.$this->league->name).'.png');
    }

    public function logoUrlLarge()
    {
        return '/img/logos/'.str_slug($this->nickname.' '.$this->league->name).'-large.png';
    }

    public function getKey()
    {
        return strtoupper('_'.str_replace(' ', '', $this->nickname).'_'.$this->id);
    }

    /**
     * Tweets all games for team on given date.
     * @param string $date
     * @return array|string - log of tweets
     */
    public function sendGameTweets(Games $game)
    {
        if(getenv('TWITTER_CONSUMER_KEY'.$this->getKey()) === false){
            echo "Skipping ".$this->nickname."\n";
            return;
        }

        // Get the config for this team's twitter account
        Twitter::reconfig([
            'consumer_key' => env('TWITTER_CONSUMER_KEY'.$this->getKey()),
            'consumer_secret' => env('TWITTER_CONSUMER_SECRET'.$this->getKey()),
            'token' => env('TWITTER_ACCESS_TOKEN'.$this->getKey()),
            'secret' => env('TWITTER_ACCESS_TOKEN_SECRET'.$this->getKey())
        ]);

        echo 'Getting tweets from '.$this->twitter."\n";

        // Get only videos from this team, including both got the order off.
        $timeline = $this->getTimeline([$game->homeTeam->twitter, $game->awayTeam->twitter]);

        // Get the video's we've checked already
        $existingTweets = TweetLogs::where('team_id', $this->id)
            ->where('created_at', '>', Carbon::now()->subHours(24))
            ->pluck('tweet_id')
            ->toArray();

        $postedTweets = [];
        foreach($timeline as $tweet) {

            // If it's a retweet, check the original instead.
            if(isset($tweet->retweeted_status)){
                $tweet = Twitter::getTweet($tweet->retweeted_status->id, ['include_entities' => 1, 'trim_user' => 1]);
            }

            // Make sure it's a tweet we want
            if(in_array($tweet->id, $existingTweets) || !$this->isValidTweet($tweet, $game)){
                continue;
            }

            $mediaUrl = $tweet->extended_entities->media[0]->expanded_url;

            echo "posting tweet ".$mediaUrl."\n";
            if (\App::environment('production'))
            {
                // Post the tweet on production
                Twitter::postTweet([
                    'status' => '#'.hashTagFormat($game->homeTeam->nickname).' '.$game->home_score.' #'.hashTagFormat($game->awayTeam->nickname).' '.$game->away_score.'                                               
                                             '.$mediaUrl
                ]);
            }

            TeamsTweets::updateOrCreate([
                'team_id' => $this->id,
                'game_id' => $game->id,
                'tweet_id' => $tweet->id,
                'media_url' => $mediaUrl
            ], [
                'period' => $game->period ? $game->period : 1
            ]);

            $postedTweets[] = $tweet;
            $existingTweets[] = $tweet->id;
        }

        if(!empty($postedTweets)){
            echo count($postedTweets) ." posted \r\n";
        }

        return $postedTweets;
    }

    /**
     * Sends a tweet if a game just started.
     * @param \App\Games $game
     * @return bool
     */
    public function sendStartTweet(Games $game)
    {
        // Login to twitter only if game hasn't started yet.
        if(!$this->reconfigTeamTwitter() || $game->ended_at){
            return false;
        }

        // Post the tweet on production
        Twitter::postTweet([
            'status' => '#'.hashTagFormat($game->homeTeam->nickname).' vs #'.hashTagFormat($game->awayTeam->nickname).' is starting soon.'
        ]);
    }

    /**
     * Tweet to send at the end of the game
     * @param \App\Games $game
     * @return bool
     */
    public function sendEndTweet(Games $game)
    {
        if(!$this->reconfigTeamTwitter() || !$game->ended_at){
            return false;
        }

        if($game->venue){
            // Draw the card with Final score
            $cardCreator = new CardCreator($game);
            $cardImage = $cardCreator->getGameCard();

            // Get media ID from twitter, required to post image.
            $media = Twitter::uploadMedia(['media' => $cardImage->stream()]);
        }


        // Post the tweet on production
        Twitter::postTweet([
            'status' => '#'.hashTagFormat($game->awayTeam->nickname).' '.$game->away_score.' #'.hashTagFormat($game->homeTeam->nickname).' '.$game->home_score.' - Final',
            'media_ids' => $media ? $media->media_id : null
        ]);
    }

    /**
     * Logs into team's twitter account, Todo: move this to the constructor or something.
     * @return bool
     */
    private function reconfigTeamTwitter()
    {
        if(getenv('TWITTER_CONSUMER_KEY'.$this->getKey()) === false){
            return false;
        }

        // Get the config for this team's twitter account
        return Twitter::reconfig([
            'consumer_key' => env('TWITTER_CONSUMER_KEY'.$this->getKey()),
            'consumer_secret' => env('TWITTER_CONSUMER_SECRET'.$this->getKey()),
            'token' => env('TWITTER_ACCESS_TOKEN'.$this->getKey()),
            'secret' => env('TWITTER_ACCESS_TOKEN_SECRET'.$this->getKey())
        ]);
    }

    private function clearTweets()
    {
        $tweets = Twitter::getUserTimeline(['count' => 200]);

        $tweetsDeleted = [];
        foreach($tweets as $tweet) {
            $tweetsDeleted[] = Twitter::destroyTweet($tweet->id);
        }

    }

    private function getTimeline($teamHandles = [])
    {
        foreach($teamHandles as $handle){
            $timelines[] = Twitter::getUserTimeline(['screen_name' => $handle, 'count' => 15, 'include_entities' => 1]);
        }

        // Merge and sort collection by most recent
        return collect(array_flatten($timelines))->sortByDesc('created_at');
    }

    private function isValidTweet($tweet, $game)
    {
        // Must have media attached and be a video
        if(!isset($tweet->extended_entities->media[0]->media_url) || $tweet->extended_entities->media[0]->type != 'video') {
            return false;
        }

        $timeOfTweet = Carbon::parse($tweet->created_at);
        // Add 10 minute buffer to start time since vids will take at least that to be posted. Don't want intro videos.
        $gameStartDate = Carbon::parse($game->start_date);
        // Add 20 minute buffer to end time since vids will take at least that to be posted.
        $gameEndDate = $game->ended_at ? Carbon::parse($game->ended_at)->addMinutes(20) : Carbon::parse('now');

        switch(false){
            case isset($tweet->extended_entities->media[0]->media_url) &&  $tweet->extended_entities->media[0]->type == 'video':
                $statusId = TweetLogs::NOT_A_VIDEO;
                break;
            case $timeOfTweet > $gameStartDate && $timeOfTweet < $gameEndDate:
                $statusId = TweetLogs::GAME_NOT_IN_PROGRESS;
                break;
            case $tweet->retweeted == false:
                $statusId = TweetLogs::RETWEETED_ALREADY;
                break;
            case $this->checkImage($tweet, $game->league->name):
                $statusId = TweetLogs::MACHINE_LEARNING_FAILED;
                break;
            default:
                $statusId = 0;
        }

        TweetLogs::updateOrCreate([
            'tweet_id' => $tweet->id,
            'media_url' => $tweet->extended_entities->media[0]->media_url,
            'team_id' => $this->id
        ],[
            'is_invalid' => $statusId,
        ]);

        if($statusId > 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns true or false if the image is a highlight or not
     * @param $tweet
     * @param $leagueName
     * @return bool|mixed
     */
    private function checkImage($tweet, $leagueName)
    {
        if(!isset($tweet->extended_entities->media[0])){
            return false;
        }
        $path = $tweet->extended_entities->media[0]->media_url;

        // Remember the results of checked tweets for 12 hours
        $output = Cache::remember('image-check-'.$tweet->id, 60 * 12, function () use ($path, $leagueName) {
            $process = new Process("python storage/machine_learning/image_script.py ".$path." ".$leagueName);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);

                return false;
            }
            $output = str_replace(array("\n", ""), '', $process->getOutput());

            // output is a string: "[0]" or "[1]"
            return (bool) $output[1];
        });

        return $output;
    }
}
