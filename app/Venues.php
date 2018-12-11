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
        return url('img/venues/'.$this->photoSlug().'.png');
    }

    public function transparentPhotoUrl()
    {
        return url('img/venues/'.$this->photoSlug().'-transparent.png');
    }

    private function photoSlug()
    {
        return str_slug($this->name.' '.$this->team->id);
    }
}