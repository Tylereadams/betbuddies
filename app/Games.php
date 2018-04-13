<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Games extends Model
{

    protected $fillable = ['home_team_id', 'home_score', 'away_team_id', 'away_score', 'league_id', 'broadcast', 'ended_at', 'start_date'];

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
            if($game->isDirty(['ended_at', 'home_score', 'away_score'])){
                switch(true){
                    case $game->ended_at:
                        $status = Games::ENDED;
                        break;
                    case $game->home_score >= 0:
                        $status = Games::IN_PROGRESS;
                        break;
                    default:
                        $status = Games::UPCOMING;
                }
                $game->status = $status;
            }
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
            'urlSegment'  => $this->urlSegment(),
            // TODO: Use the actual game location instead, needs to be imported.
            'location' => $this->homeTeam->location,
            'homeTeam'  => [
                'id'    => $this->homeTeam->id,
                'name'  => $this->homeTeam->nickname,
                'score'  => $this->home_score,
                'colors'    => $this->homeTeam->colors->map(function($color){
                    return  $color->hex;
                }),
                'thumbUrl' => $this->homeTeam->logoUrl(),
                'spread' => (int) $this->getTeamSpread('home'),
                'isWinner' => $this->home_score > $this->away_score && ($this->ended_at) ? 1 : 0
            ],
            'awayTeam' => [
                'id'    => $this->awayTeam->id,
                'name'  => $this->awayTeam->nickname,
                'thumbUrl' => $this->awayTeam->logoUrl(),
                'score'  => $this->away_score,
                'colors'    => $this->awayTeam->colors->map(function($color){
                    return  $color->hex;
                }),
                'spread' => (int) $this->getTeamSpread('away'),
                'isWinner' => $this->away_score > $this->home_score && ($this->ended_at) ? 1 : 0
            ],
            'bets' => $this->bets->map(function($bet){
                return $bet->getCardData();
            }),
            'startDate' => $startDate->format('D M j'),

            'startTime' => $startDate->format('g:ia'),
            'endedAt' => $this->ended_at
        ];

        return $cardData;
    }


    public function isBettable()
    {
        // If now is greater than start time, don't allow the bet.
        if(Carbon::now()->gt(Carbon::parse($this->start_date))){
            return false;
        }

        return true;
    }

}