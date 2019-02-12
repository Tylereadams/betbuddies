<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamCredentials extends Model
{
    protected $guarded = [];

    public function team()
    {
        return $this->belongsTo(Teams::class);
    }
}