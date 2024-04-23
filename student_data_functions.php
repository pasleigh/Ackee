<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 28/12/2016
 * Time: 10:51
 *
 * Manipulate the stuent data
 *
 */

function GetLevelFromModuleList($module_list){
    // Loop through the module list to get the
    // list of numbers

    //echo("<pre>");
    //var_dump($module_list);
    if(is_array($module_list)) {
        $levels = array();
        foreach ($module_list as $module) {
            // get the 5th character
            $level = substr($module, 4, 1);
            if (is_numeric($level)) {
                $levels[] = intval($level);
            }
        }
        //var_dump($levels);
        if(sizeof($levels)>1) {
            $c = array_count_values($levels);
            $level = array_search(max($c), $c);
        }else{
            $level = $levels[0];
            if(!is_numeric($level)){
                $level = 0;
            }
        }
    }else{
        $level = 0;
    }
    //echo("c = $c<BR>level = $level<BR>");
    //exit();
    return $level;
}

