<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Relations
     */

    // Bets user has created
    public function bets()
    {
        return $this->hasMany(UsersBets::class);
    }

    // Bets user has accepted
    public function acceptedBets()
    {
        return $this->hasMany(UsersBets::class, 'opponent_id');
    }

    public function allBets() {
        return $this->bets->merge($this->acceptedBets());
    }


    /**
     * Modifiers
     */

    public function setUrlSegmentAttribute()
    {
        $this->attributes['url_segment'] = $this->createUrlSegment();
    }

    /**
     * Accessors
     */
    public function getUrlSegmentAttribute()
    {
        // Create URL segment if we don't have one.
        if(!$this->url_segment){
            $this->createUrlSegment();
        }

        return $this->url_segment;
    }

    /**
     * Creates URL safe string for game: 'homeTeam-awayTeam-date'
     * @return mixed
     */
    public function createUrlSegment()
    {
        // Create URL segment if we don't have one.
        $newSegment = str_slug($this->name);

        // How many of these segments exists already
        $count = count(User::whereRaw("url_segment REGEXP '^{$newSegment}(-[0-9]+)?$' and id != '{$this->id}'")->get());

        // More than 1, increment by the count
        $this->url_segment = $count > 0 ? $newSegment.'-'.$count : $newSegment;
        $this->save();

        return $this->url_segment;
    }


    /**
     * Create avatar url
     */
    public function avatarUrl()
    {
        return 'https://graph.facebook.com/v2.8/'.$this->avatar.'/picture?type=normal';
    }

    public function getCardData()
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar
        ];

        return $data;
    }

    public function getBetWinnings()
    {
        $totalWinnings = 0;
        foreach($this->allBets() as $bet){
            if(!$bet->game->ended_at){
                continue;
            }

            $winningUser = $bet->getWinningUser();

            // Continue if hasn't been accepted
            if(!$winningUser){
                continue;
            }

            // User won, add, else subtract
            if($winningUser->id == Auth::id()){
                $totalWinnings = $totalWinnings + $bet->amount;
            } else {
                $totalWinnings = $totalWinnings - $bet->amount;
            }
        }

        return $totalWinnings;
    }


}
