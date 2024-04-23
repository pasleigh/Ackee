<?php
// Turn error reportiing on/off
error_reporting(E_ALL);
//ini_set('display_errors', '1');
//error_reporting(0);
//ini_set('display_errors', '0');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

(@include_once("./staff_student_functions.php")) OR die("Cannot open ./staff_student_functions.php from setup_params.php<BR>");

// Some standard table names
$setup_params = "params";
$marks_index = "marks_index";
$categories = "categories";
$admin_users = "admin_users";
$definition = "definition";
$tutor_initials = "tutor_initials";
// Staff list
$staff = "staff_names"; // Generic list (will get old)
$staff_names = $staff;
// Student list
$students = "student_names";

if (array_key_exists('pm_id', $_REQUEST) == false) {
    $_REQUEST['pm_id'] = null;
}
$pm_id = $_REQUEST['pm_id'];

$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
//echo("Project/module $pm_id<BR>");
// We need to do something should the module not be specified
// Take the first (last?) entry in the  $project_setup table
if ($pm_id == "") {
    // Get last entry SELECT * FROM mytable ORDER BY id DESC LIMIT 1

    $query = "SELECT * FROM $setup_params ORDER BY id DESC LIMIT 1";
    try {
        $results = $db->query($query);

    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");

        exit();
    }

    $results_data = $results->fetchAll();
    //$results->closeCursor();
    //var_dump($results_data);
    $num = $results->rowCount();
    //echo($num . "<BR>");
    //$i = 0;
    foreach($results_data as $result_data) {
        //echo($i . "<BR>");
        $year_begin = $result_data['year_begin'];
        $year_end = $result_data['year_end'];
        $num_modules = $result_data['num_modules'];
        $num_years = $result_data['num_years'];
        $visible = $result_data['visible'];

        $pm_id = $result_data['id'];
        //$i++;
    }
}

$query = "select * from $setup_params where 1";//id=$pm_id;
//echo($query);
try {
    $results = $db->query($query);
} catch (PDOException $ex) {
    $this_function = __FUNCTION__;
    echo "An Error occured accessing the database in function: $this_function <BR>\n";
    echo(" Query = $query<BR> \n");
    echo(" Err message: " . $ex->getMessage() . "<BR>\n");

    exit();
}

$setup_ress = $results->fetchAll();
//var_dump($setup_ress);
$low_attendance_value = array();
$low_numbers_value = array();
foreach($setup_ress as $setup_res) {
    $id = $setup_res['id'];
    $year_begin = $setup_res['year_begin'];
    $year_end = $setup_res['year_end'];
    $num_modules = $setup_res['num_modules'];
    $num_years = $setup_res['num_years'];
    $num_students = $setup_res['num_students'];
    $visible = $setup_res['visible'];

    $low_attendance_value[$id] = $setup_res['low_attendance_value'];
    $low_numbers_value[$id] = $setup_res['low_numbers_value'];

    // setup the weeks
    $term_1_sunday_start =  $setup_res['term_1_sunday_start'];
    $term_2_sunday_start =  $setup_res['term_2_sunday_start'];
    $term_3_sunday_start =  $setup_res['term_3_sunday_start'];
    $term_1_num_weeks =  $setup_res['term_1_num_weeks'];
    $term_2_num_weeks =  $setup_res['term_2_num_weeks'];
    $term_3_num_weeks =  $setup_res['term_3_num_weeks'];
}

// setup the weeks array

if (!defined('PHP_VERSION_ID')) {
    $php_version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($php_version[0] * 10000 + $php_version[1] * 100 + $php_version[2]));
}
//echo("php version" . PHP_VERSION_ID);
if(PHP_VERSION_ID < 50200){
    // cannot use Date Time
    //echo($term_1_sunday_start ."<BR>");
    $day_start = intval(date('j',strtotime($term_1_sunday_start)));
    $month_start = intval(date('n',strtotime($term_1_sunday_start)));
    $year_start = intval(date('Y',strtotime($term_1_sunday_start)));
    //echo("year: $year_start month: $month_start day: $day_start<BR>");
    for ($i = 0; $i < $term_1_num_weeks; $i++) {
        $j = $i;
        $weeks_str[$i] = date('Y-m-d H:i:s', mktime(0,0,0, $month_start, $day_start+$i*7, $year_start));
        //echo($j . ": " . $weeks_str[$i] ."<BR>");
    }

    $day_start = intval(date('j',strtotime($term_2_sunday_start)));
    $month_start = intval(date('n',strtotime($term_2_sunday_start)));
    $year_start = intval(date('Y',strtotime($term_2_sunday_start)));
    //echo("year: $year_start month: $month_start day: $day_start<BR>");
    for ($i = 0; $i < $term_2_num_weeks; $i++) {
        $j = $term_1_num_weeks+$i;
        $weeks_str[$j] = date('Y-m-d H:i:s', mktime(0,0,0, $month_start, $day_start+$i*7, $year_start));
        //echo($j . ": " . $weeks_str[$j] ."<BR>");
    }

    $day_start = intval(date('j',strtotime($term_3_sunday_start)));
    $month_start = intval(date('n',strtotime($term_3_sunday_start)));
    $year_start = intval(date('Y',strtotime($term_3_sunday_start)));
    //echo("year: $year_start month: $month_start day: $day_start<BR>");
    for ($i = 0; $i <= $term_3_num_weeks; $i++) {
        $j = $term_1_num_weeks+$term_2_num_weeks+$i;
        $weeks_str[$j] = date('Y-m-d H:i:s', mktime(0,0,0, $month_start, $day_start+$i*7, $year_start));
        //echo($j . ": " . $weeks_str[$j] ."<BR>");
    }

}else {
    // Use Date Time as its better
    //echo("<BR>using Datetime<BR>");
    $day = '+1 day';//new DateInterval('P1D');
    $seven_days = '+7 days';//new DateInterval('P7D');
    $weeks[0] = new DateTime($term_1_sunday_start);
    for ($i = 1; $i <= $term_1_num_weeks; $i++) {
        $weeks[$i] = clone $weeks[$i - 1];
        $weeks[$i]->modify($seven_days);
    }

    $weeks[$term_1_num_weeks] = new DateTime($term_2_sunday_start);
    for ($i = $term_1_num_weeks + 1; $i <= $term_1_num_weeks + $term_2_num_weeks; $i++) {
        $weeks[$i] = clone $weeks[$i - 1];
        $weeks[$i]->modify($seven_days);
    }

    $weeks[$term_1_num_weeks + $term_2_num_weeks] = new DateTime($term_3_sunday_start);
    for ($i = $term_1_num_weeks + $term_2_num_weeks + 1; $i <= $term_1_num_weeks + $term_2_num_weeks + $term_3_num_weeks; $i++) {
        $weeks[$i] = clone $weeks[$i - 1];
        $weeks[$i]->modify($seven_days);
    }

    // save the strings
    for($i = 0 ; $i<= $term_1_num_weeks+$term_2_num_weeks+$term_3_num_weeks ; $i++){
        $weeks_str[$i] = $weeks[$i]->format('Y-m-d H:i:s');
        //echo($i . ": " . $weeks_str[$i] ."<BR>");
    }

}
//for($i = 0 ; $i<= $term_1_num_weeks+$term_2_num_weeks+$term_3_num_weeks ; $i++){
//    echo $weeks[$i]->format('Y-m-d H:i:s') . "<BR>";
//}
/*
echo("<pre>");
print_r($_SERVER);
echo("</pre>");
*/
$this_username = $_SERVER['REMOTE_USER'];

$is_staff = IsStaff($db, $this_username, $staff_names);
$is_student = IsStudent($db, $this_username, $students);


if($is_staff){
    $this_fullname = GetStaffNameFromUsername($db, $this_username, $staff_names, false);
    $this_staff_id = GetStaffIDFromUsername($db, $this_username, $staff_names);
    $admin_level = GetSuperAdminLevel($db, $this_username);
    $access = access_check_response($db,$this_username, $this_fullname, $staff_names, $access_phrase);
    $is_admin = $access;
}else if($is_student){
    $this_fullname = GetStudentNameFromUsername($db, $this_username, $students);
    $this_sid = GetStudentIDFromUsername($db, $this_username, $students);
    $admin_level = 0;
    $is_admin = false;
}else{
    $this_fullname = "not known";
    $this_sid = "not_known";
    $admin_level = 0;
    $is_admin = false;
    $access_phrase = "You do not have access to this functionality";
    $access = false;
}

$root_page = "index.php";

function print_r2($var){
    echo("<pre>");
    print_r($var);
    echo("</pre>");
}
?>