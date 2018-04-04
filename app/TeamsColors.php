<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamsColors extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['team_id', 'hex'];

    // Relations
    public function team()
    {
        return $this->belongsTo(Teams::class);
    }

}
