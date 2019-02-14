<?php

namespace App;

use App\Services\CardCreator;
use Illuminate\Database\Eloquent\Model;
use Thujohn\Twitter\Facades\Twitter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Teams extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['nickname', 'location', 'latitude', 'longitude', 'hashtag', 'league'];

    protected $primaryKey = 'id';


    const BUFFER_MINUTES = 20;
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

    public function credentials() {
        return $this->hasOne(TeamCredentials::class, 'team_id');
    }

    public function tweets()
    {
        return $this->hasMany(TeamsTweets::class, 'team_id');
    }

    public function tweetLogs()
    {
        return $this->hasMany(TweetLogs::class, 'team_id');
    }

    public function venue()
    {
        return $this->hasOne(Venues::class, 'team_id');
    }

    public function players()
    {
        return $this->hasMany(Players::class, 'team_id');
    }

    public function homeGames()
    {
        return $this->hasMany(Games::class, 'home_team_id');
    }

    public function awayGames()
    {
        return $this->hasMany(Games::class, 'away_team_id');
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
    public function sendTweets()
    {
        if(!isset($this->credentials->token) || !isset($this->credentials->token_secret)) {
            return false;
        }

        Twitter::reconfig(['token' => $this->credentials->token, 'secret' => decrypt($this->credentials->token_secret)]);

        // Get unsent tweets in the last 72 hours
        $unsentTweets = TweetLogs::where('team_id', $this->id)
            ->where('created_at', '>', Carbon::now()->subHours(72))
            ->whereNull('sent_at')
            ->whereNotNull('game_id')
            ->orderBy('created_at', 'DESC')
            ->get();

        if(!$unsentTweets) {
            return;
        }

        echo "sending ".$this->nickname." tweets.\n";

        $postedTweets = [];
        foreach($unsentTweets as $unsentTweet) {

            // Make sure game is updated more recently than when the tweet was created so we have more accurate scores
            if($unsentTweet->game->updated_at->get($unsentTweet->created_at)) {
                continue;
            }

            echo "posting tweet ".$unsentTweet->video_url."\n";
            if (\App::environment('production'))
            {
                // Post the tweet on production
                if(Twitter::postTweet([
                    'status' => '#'.hashTagFormat($unsentTweet->game->homeTeam->nickname).' '.$unsentTweet->game->home_score.' #'.hashTagFormat($unsentTweet->game->awayTeam->nickname).' '.$unsentTweet->game->away_score.'                                               
                                             '.$unsentTweet->getTweetUrl().'/video/1'
                ])) {
                    $unsentTweet->sent_at = Carbon::now();
                    $unsentTweet->save();
                };
            }

            $postedTweets[] = $unsentTweet;
        }

        echo count($postedTweets) ." posted \r\n";

        return $postedTweets;
    }


    /**
     * Tweet to send at the end of the game
     * @param \App\Games $game
     * @return bool
     */
    public function sendEndTweet(Games $game)
    {
        if(!isset($this->credentials->token) || !isset($this->credentials->token_secret)) {
            return false;
        }

        Twitter::reconfig(['token' => $this->credentials->token, 'secret' => decrypt($this->credentials->token_secret)]);

        echo $this->nickname." sending final tweet\n";

        // Draw the card with Final score
        $cardCreator = new CardCreator($game);
        $cardImage = $cardCreator->getGameCard();

        $media = null;
        if($cardImage) {
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
     * Gets the timeline of given twitter handles (no preceding @ is needed for the handles)
     * @param array $teamHandles
     * @return static
     */
    public function getTimeline($teamHandles = [])
    {
        foreach($teamHandles as $handle){
            // Cache the twitter timeline for 3 minutes so we don't hit a rate limit
            $timelines[] = Cache::remember($handle.'-twitter-timeline', 3, function () use($handle) {
                    return Twitter::getUserTimeline(['screen_name' => $handle, 'count' => 50, 'include_entities' => 1]);
                });
        }

        // Merge and sort collection by most recent
        return collect(array_flatten($timelines))->sortByDesc('created_at');
    }

    public function isValidTweet($tweet, $game)
    {
        $timeOfTweet = Carbon::parse($tweet->created_at);
        // Add 10 minute buffer to start time since vids will take at least that to be posted. Don't want intro videos.
        $gameStartDate = Carbon::parse($game->start_date);
        // Add 20 minute buffer to end time since vids will take at least that to be posted.
        $gameEndDate = $game->ended_at ? Carbon::parse($game->ended_at)->addMinutes(Self::BUFFER_MINUTES) : Carbon::parse('now');

        $isAVideo = (isset($tweet->extended_entities->media[0]->media_url) &&  $tweet->extended_entities->media[0]->type == 'video');
        $gameHasStarted = ($timeOfTweet > $gameStartDate && $timeOfTweet < $gameEndDate);

        if(!$isAVideo || !$gameHasStarted || $tweet->retweeted) {
            return false;
        }

        // Google Vision here.
        if(!$this->checkImage($tweet, $game->league->name)) {
            return false;
        }

        TweetLogs::updateOrCreate([
            'tweet_id' => $tweet->id,
            'media_url' => $tweet->extended_entities->media[0]->media_url,
            'team_id' => $this->id,
            'text' => $tweet->text,
        ]);

        return true;
    }
}
