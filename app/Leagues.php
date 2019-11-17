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

    public function getPeriodLabel($period)
    {
        if(($this->id == Self::NBA_ID || $this->id == Self::NFL_ID) && $period > 4){
            return 'OT';
        } elseif($this->id == Self::NHL_ID  && $period > 3){
            return 'OT';
        } else {
            return $this->period_label;
        }
    }

    public function getTotalPeriods()
    {
        switch($this->id) {
            case Self::NBA_ID || Self::NFL_ID:
                return 4;
                break;
            case Self::MLB_ID:
                return 9;
                break;
            case Self::NHL_ID:
                return 3;
                break;
        }
    }
}
