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
    protected $fillable = [
        'name', 'email', 'password',
    ];

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
