<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
    //
    protected $guarded = ['id'];

    protected $primaryKey = 'user_id';


    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getWinPercentage()
    {
        if(!$this->wins){
            return 0;
        }

        return $this->wins / ($this->wins + $this->losses);
    }

}
