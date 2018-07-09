<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venues extends Model
{
    //
    protected $guarded = [];

    protected $primaryKey = 'team_id';

    public $timestamps = false;


    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    public function photoUrl()
    {
        return '/img/venues/'.$this->photoSlug().'.png';
    }

    public function transparentPhotoUrl()
    {
        return '/img/venues/'.$this->photoSlug().'-transparent.png';
    }


    private function photoSlug()
    {
        return str_slug($this->team->id.' '.$this->name);
    }
}