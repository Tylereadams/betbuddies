<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leagues extends Model
{
    public $timestamps = false;

    /**
     * Relations
     */
    public function games(){
        return $this->hasMany(Games::class, 'league_id');
    }
}
