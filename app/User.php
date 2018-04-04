<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
    public function bets()
    {
        return $this->hasMany(UsersBets::class);
    }

    /**
     * Create avatar url
     */
    public function avatarUrl()
    {
        return 'https://graph.facebook.com/v2.8/'.$this->avatar.'/picture?type=normal';
    }
}
