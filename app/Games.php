<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Games extends Model
{

    protected $guarded = ['id'];

    protected $primaryKey = 'id';

    const UPCOMING = 1;
    const IN_PROGRESS = 2;
    const ENDED = 3;
    const POSTPONED = 4;

    /**
     * Register any events for this model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Update the status of the game if ended_at or the score changed
        static::updating(function ($game) {
            $status = Games::UPCOMING;

            if($game->isDirty(['home_score', 'away_score'])){
                    $status = Games::IN_PROGRESS;
            }

            // Send end of game tweets
            if($game->isDirty(['ended_at'])){
                $status = Games::ENDED;

                // Remember if we sent the tweet for 24 hours
                Cache::remember($game->id.'-sent-final-tweet', 60 * 24, function () use($game) {
                    $game->homeTeam->sendEndTweet($game);
                    $game->awayTeam->sendEndTweet($game);
                    return true;
                });

            }

            $game->status = $status;
        });
    }


    /**
     * Relations
     */
    public function homeTeam()
    {
        return $this->belongsTo(Teams::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Teams::class, 'away_team_id');
    }

    public function league()
    {
        return $this->belongsTo(Leagues::class);
    }

    public function bets()
    {
        return $this->hasMany(UsersBets::class, 'game_id');
    }

    public function setUrl_segmentAttribute()
    {
        $this->attributes['url_segment'] = $this->urlSegment();
    }

    /**
     * Creates URL safe string for game: 'homeTeam-awayTeam-date'
     * @return mixed
     */
    public function urlSegment()
    {
        // Create URL segment if we don't have one.
        if(!$this->url_segment){
            $this->url_segment = str_slug($this->homeTeam->nickname.' '.$this->awayTeam->nickname.' '.Carbon::parse($this->start_date)->format('Y-m-d Hi'));
            $this->save();
        }

        return $this->url_segment;
    }

    public function statusName()
    {
        switch($this->status){
            case GAMES::UPCOMING:
                $status = 'upcoming';
                break;
            case GAMES::IN_PROGRESS:
                $status = 'in progress';
                break;
            case GAMES::ENDED:
                $status = 'ended';
                break;
            case GAMES::POSTPONED:
                $status = 'postponed';
                break;
            default:
                $status = 'upcoming';
        }

        return $status;
    }

    /**
     * Get the game's period in sentence form.
     *
     * @param  string  $value
     * @return string
     */
    public function getGameTimeStringAttribute()
    {
        return ordinalNumber($this->period).' '.$this->league->period_label;
    }

    /**
     * Returns a formatted spread.
     * @param string $team
     * @return string
     */
    public function getTeamSpread($team = 'home')
    {
        switch(strtolower($team)){
            case 'home':
                $spread = $this->home_spread;
                break;
            case 'away':
                $spread = $this->away_spread;
                break;
        }

        return formatSpread($spread);
    }

    public function getCardData()
    {
        $startDate = Carbon::parse($this->start_date);
        $cardData = [
            'id'    => $this->id,
            'league'    => [
                'name' => $this->league->name,
                'periodLabel' => $this->league->period_label
            ],
            'period' => $this->period ? ordinalNumber($this->period) : null,
            'status' => $this->statusName(),
            'urlSegment'  => $this->url_segment,
            'location' => $this->homeTeam->location,
            'homeTeam'  => [
                'id'    => $this->homeTeam->id,
                'name'  => $this->homeTeam->nickname,
                'score'  => $this->home_score,
                'thumbUrl' => $this->homeTeam->logoUrlLarge(),
                'spread' => (int) $this->getTeamSpread('home'),
                'isWinner' => $this->home_score > $this->away_score && ($this->ended_at) ? true : false,
                'betCount' => $this->bets()->where(function($q){
                    $q->where('opponent_team_id', $this->home_team_id);
                    $q->orWhere('team_id', $this->home_team_id);
                })->count()
            ],
            'awayTeam' => [
                'id'    => $this->awayTeam->id,
                'name'  => $this->awayTeam->nickname,
                'score'  => $this->away_score,
                'thumbUrl' => $this->awayTeam->logoUrlLarge(),
                'spread' => (int) $this->getTeamSpread('away'),
                'isWinner' => $this->away_score > $this->home_score && ($this->ended_at) ? true : false,
                'betCount' => $this->bets()->where(function($q){
                    $q->where('opponent_team_id', $this->away_team_id);
                    $q->orWhere('team_id', $this->away_team_id);
                })->count()
            ],
            'bets' => $this->bets->map(function($bet){
                return $bet->getCardData();
            }),
            'isBettable' => $this->isBettable(),
            'broadcast' => $this->broadcast,
            'startDate' => $startDate->format('D M j'),
            'startTime' => $startDate->format('g:ia'),
            'endedAt' => $this->ended_at
        ];

        return $cardData;
    }


    public function isBettable()
    {
        // If now is greater than start time or doesn't have a status of 1, don't allow the bet.
        if(Carbon::now()->gt(Carbon::parse($this->start_date))){
            return false;
        }

        return true;
    }

    public function isWinner(Teams $team)
    {
        if(!$this->ended_at){
            return false;
        }
        // Home is winner
        if($this->home_score > $this->away_score){
            return $team->id == $this->homeTeam->id;
        } else if($this->away_score > $this->home_score) {
            return $team->id == $this->awayTeam->id;
        } else {
            return false;
        }
    }

}