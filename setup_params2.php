<?php
echo("here in setup params<BR>");
// Turn error reportiing on/off
error_reporting(E_ALL);
//ini_set('display_errors', '1');
//error_reporting(0);
//ini_set('display_errors', '0');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo("1");

(@include_once("./staff_student_functions.php")) OR die("Cannot open ./staff_student_functions.php from setup_params.php<BR>");


