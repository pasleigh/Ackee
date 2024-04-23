<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 08/07/2016
 * Time: 16:26
 */
function Median($Array) {
    return Quartile_50($Array);
}

function Quartile_25($Array) {
    return Quartile($Array, 0.25);
}

function Quartile_50($Array) {
    return Quartile($Array, 0.5);
}

function Quartile_75($Array) {
    return Quartile($Array, 0.75);
}

function IQR($Array) {
    return (Quartile_75($Array) - Quartile_25($Array));
}

function Quartile($Array, $Quartile) {
    sort($Array);
    $pos = (count($Array) - 1) * $Quartile;

    $base = floor($pos);
    $rest = $pos - $base;

    if( isset($Array[$base+1]) ) {
        return $Array[$base] + $rest * ($Array[$base+1] - $Array[$base]);
    } else {
        return $Array[$base];
    }
}

function BoxPlot5Stats($Array)
{
    // Returns:
    // min, 25 quartile, 50 quartile (median), 75 quartile, max
    //echo("<PRE>");
    //print_r($Array);
    //echo("</PRE>");
    $FiveStats = array();
    $FiveStats[0] = min($Array);
    $FiveStats[1] = Quartile_25($Array);
    $FiveStats[2] = Quartile_50($Array);
    $FiveStats[3] = Quartile_75($Array);
    $FiveStats[4] = max($Array);

    return $FiveStats;
}

function Average($Array) {
    return array_sum($Array) / count($Array);
}

function StdDev($Array) {
    if( count($Array) < 2 ) {
        return 0;
    }

    $avg = Average($Array);

    $sum = 0;
    foreach($Array as $value) {
        $sum += pow($value - $avg, 2);
    }

    return sqrt((1 / (count($Array) - 1)) * $sum);
}