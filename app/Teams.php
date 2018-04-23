<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Games;
use Thujohn\Twitter\Facades\Twitter;
use App\TeamsTweets;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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


    /**
     * Functions
     */
    public function logoUrl()
    {
        return asset('img/logos/'.str_slug($this->nickname.' '.$this->league->name).'.png');
    }

    public function getKey()
    {
        return strtoupper('_'.$this->nickname.'_'.$this->id);
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
        $timeline = $this->getTimeline([$this->twitter]);

        // Get the video's we've tweeted already
        $existingTweets = TeamsTweets::where('game_id', $game->id)
            ->where('team_id', $this->id)
            ->pluck('tweet_id')
            ->toArray();

        $postedTweets = [];
        echo "Checking tweets... \n";
        foreach($timeline as $tweet) {
            // Check the original tweet if this is a retweet, assign it if that's what we want
            if(isset($tweet->retweeted_status)){
                $tweet = Twitter::getTweet($tweet->retweeted_status->id, ['include_entities' => 1, 'trim_user' => 1]);
                if(!$tweet){
                    continue;
                }
            }

            // Make sure it's a tweet we want or haven't tweeted already
            if(!$this->isValidTweet($tweet, $game) || in_array($tweet->id, $existingTweets)){
                continue;
            }

            $mediaUrl = $tweet->extended_entities->media[0]->expanded_url;

            echo "posting tweet ".$mediaUrl."\n";
            // Post the tweet
//            Twitter::postTweet([
//                'status' => '#'.$game->homeTeam->nickname.' #'.$game->awayTeam->nickname.' '.$mediaUrl
//            ]);

            TeamsTweets::firstOrCreate([
                'team_id' => $this->id,
                'game_id' => $game->id,
                'tweet_id' => $tweet->id,
                'period' => $game->period,
                'media_url' => $mediaUrl
            ]);

            $postedTweets[] = $tweet;
            $existingTweets[] = $tweet->id;
        }


        if(!empty($postedTweets)){
            echo count($postedTweets) ." posted \r\n";
        }

        return $postedTweets;
    }

    private function clearTweets()
    {
        TeamsTweets::where('team_id', $this->id)->delete();

        $tweets = Twitter::getUserTimeline(['count' => 200]);

        $tweetsDeleted = [];
        foreach($tweets as $tweet) {
            $tweetsDeleted[] = Twitter::destroyTweet($tweet->id);
        }

    }

    private function getTimeline($teamHandles = [])
    {
//        foreach($teamHandles as $handle){
//            $timelines[] = collect(Twitter::getUserTimeline(['screen_name' => $handle, 'count' => 200]));
//        }

        // Merge and sort collection by most recent
//        return $timelines[0]->merge($timelines[1])->sortByDesc('created_at');
        return collect(Twitter::getUserTimeline(['screen_name' => $teamHandles[0], 'count' => 70, 'include_entities' => 1]))->sortByDesc('created_at');
    }

    private function isValidTweet($tweet, $game)
    {
        $timeOfTweet = Carbon::parse($tweet->created_at);
        // Add 10 minute buffer to start time since vids will take at least that to be posted. Don't want intro videos.
        $gameStartDate = Carbon::parse($game->start_date);
        // Add 10 minute buffer to end time since vids will take at least that to be posted. Don't want intro videos.
        $gameEndDate = Carbon::parse($game->ended_at)->addMinutes(10);

        if($timeOfTweet > $gameStartDate // Tweet posted after game has started
            && $timeOfTweet < $gameEndDate // Tweet posted before game ended
            && $tweet->retweeted == false // Didn't retweet ourselves
            && isset($tweet->extended_entities->media[0]->media_url) // Has media attached
            && $tweet->extended_entities->media[0]->type == 'video' // Media is a video
            && $this->checkImage($tweet, $game->league->name) // Machine learning script to identify a highlight
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function checkImage($tweet, $leagueName)
    {
        if(!isset($tweet->extended_entities->media[0])){
            return false;
        }
        $path = $tweet->extended_entities->media[0]->media_url;

        $process = new Process("python storage/machine_learning/image_script.py ".$path." ".$leagueName);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = str_replace(array("\n", ""), '', $process->getOutput());

        // output is a string: "[0]" or "[1]"
        return (bool) $output[1];
    }
}
