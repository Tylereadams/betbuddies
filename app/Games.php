<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Games extends Model
{

    protected $guarded = ['id'];

    protected $primaryKey = 'id';

    protected $dates = ['start_date'];

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

        // Update the status of the game whenever it changes
        static::updating(function ($game) {

            $pastStartDate = Carbon::now() > $game->start_date;

            if($pastStartDate && !$game->ended_at){ // Game started - current time greater than start time
                $status = Games::IN_PROGRESS;
            } elseif ($pastStartDate && $game->ended_at){ // Game ended
                $status = Games::ENDED;

                // Update game's bets
                $game->updateBets();

            } else { // Game upcoming
                $status = Games::UPCOMING;
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

    public function tweets()
    {
        return $this->hasMany(TweetLogs::class,  'game_id');
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
    public function getUrlSegment()
    {
        // Create URL segment if we don't have one.
        if(!$this->url_segment){
            $this->url_segment = str_slug($this->homeTeam->nickname.' '.$this->awayTeam->nickname.' '.Carbon::parse($this->start_date)->format('Y-m-d Hi'));
            $this->save();
        }

        return $this->url_segment;
    }

    public function getPlayers()
    {
        $homePlayers = $this->homeTeam->players;
        $awayPlayers = $this->awayTeam->players;

        return $awayPlayers->merge($homePlayers);
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
                'periodLabel' => $this->league->getPeriodLabel($this->period)
            ],
            'period' => $this->period ? ordinalNumber($this->period) : null,
            'status' => $this->statusName(),
            'urlSegment'  => $this->getUrlSegment(),
            'location' => $this->homeTeam->location,
            'homeTeam'  => [
                'id'    => $this->homeTeam->id,
                'name'  => $this->homeTeam->nickname,
                'score'  => $this->home_score,
                'thumbUrl' => $this->homeTeam->logoUrl(),
                'spread' => (int) $this->getTeamSpread('home'),
                'isWinner' => $this->home_score > $this->away_score && ($this->ended_at) ? true : false,
                'betCount' => $this->bets->filter(function ($bet) {
                    return in_array($this->home_team_id, [$bet->opponent_team_id, $bet->team_id]);
                })->count()
            ],
            'awayTeam' => [
                'id'    => $this->awayTeam->id,
                'name'  => $this->awayTeam->nickname,
                'score'  => $this->away_score,
                'thumbUrl' => $this->awayTeam->logoUrl(),
                'spread' => (int) $this->getTeamSpread('away'),
                'isWinner' => $this->away_score > $this->home_score && ($this->ended_at) ? true : false,
                'betCount' => $this->bets->filter(function ($bet) {
                    return in_array($this->away_team_id, [$bet->opponent_team_id, $bet->team_id]);
                })->count()
            ],
            'bets' => $this->bets->map(function($bet){
                return $bet->getCardData();
            }),
            'betAmount' => $this->bets->pluck('amount')->sum(),
            'highlightsCount' => $this->tweets->count(),
            'isBettable' => $this->isBettable(),
            'broadcast' => $this->broadcast,
            'startDate' => !$startDate->isToday() ? $startDate->format('n/j') : '',
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

    public function updateBets()
    {
        $bets = UsersBets::where('game_id', $this->id)->get();

        foreach($bets as $bet) {

            if(!$bet->opponent_id){
                continue;
            }

            $winningUser = $bet->getWinner();
            $losingUser = $winningUser->id != $bet->user_id ? $bet->user : $bet->opponent;

            if($winningUser && $losingUser) {
                // Update bet with the winner and loser
                $bet->winning_user_id = $winningUser->id;
                $bet->losing_user_id = $losingUser->id;

                $bet->save();
            }
        }
    }


}