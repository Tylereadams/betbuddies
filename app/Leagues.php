<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leagues extends Model
{
    public $timestamps = false;

    const NBA_ID = 1;
    const MLB_ID = 2;
    const NFL_ID = 3;
    const NHL_ID = 4;

    /**
     * Relations
     */
    public function games(){
        return $this->hasMany(Games::class, 'league_id');
    }


    /**
     * Creates the icon name for this league
     * @return string
     */
    public function getIconName()
    {
        $iconSuffix = 'ball';

        if($this->long_name == 'hockey'){
            $iconSuffix = 'puck';
        }
        return 'fa-'.$this->long_name.'-'.$iconSuffix;
    }
}
