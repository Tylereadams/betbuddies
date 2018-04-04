<?php

/**
 * Adds +/- if number is positive or negative. Used for the spread.
 * @param $num
 * @return string
 */
function formatSpread($num){

    if($num == 0){
        return 'E';
    } elseif ($num > 0) {
        $num = '+'.$num;
    } else {
    }

    return sprintf("%+s", $num);
}

/**
 * Returns ordinal (1st, 2nd, 3rd, 4th, etc...) number
 * @param $number
 * @return string
 */
function ordinalNumber($number)
{
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13)){
        return $number. 'th';
    } else {
        return $number. $ends[$number % 10];
    }
}