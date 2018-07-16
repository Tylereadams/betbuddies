<?php

/**
 * Adds +/- if number is positive or negative. Used for the spread.
 * @param $num
 * @param $oppositeSpread
 * @return string
 */
function formatSpread($num, $oppositeSpread = false){

    if($num == 0){
        return 'E';
    } elseif ($num > 0) {
        $num = '+'.$num;
    } else {
    }

    if($oppositeSpread){
        $num = $num * -1;
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

/**
 * Converts string into hashtag friendly
 * No spaces
 * @param $string
 * @return mixed
 */
function hashTagFormat($string)
{
    return str_replace(' ', '', $string);
}

/**
 * Converts HEX to RGB
 * @param $colour
 * @return array|bool
 */
function hex2rgb( $colour ) {
    if ( $colour[0] == '#' ) {
        $colour = substr( $colour, 1 );
    }
    if ( strlen( $colour ) == 6 ) {
        list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
    } elseif ( strlen( $colour ) == 3 ) {
        list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
    } else {
        return false;
    }
    $r = hexdec( $r );
    $g = hexdec( $g );
    $b = hexdec( $b );
    return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}