<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 12/07/2016
 * Time: 22:21
 */

// need:
// $module_means
// $module_iqrs
// $module_summary_names
// $plot_title

// Get data for a student for plotting the module box plots only for that studenet with their data on


(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

if(isset($_POST['get_pm_id'])) {
    $pm_id = $_POST['get_pm_id'];
    $sid = $_POST['get_sid'];

    $query = "SELECT * FROM $marks_index WHERE id='$pm_id'";
//echo("echo: $query<BR>");
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");

        exit();
    }
    $get_data = $results->fetch();

    $year_begin = $get_data['year_begin'];
    $year_end = $get_data['year_end'];
    $year_end_short = $year_end - 2000;
    $got_summary_table = $get_data['got_summary_table'];
    $got_module_table = $get_data['got_modules_table'];
    $version = $get_data['version'];
    $level = $get_data['level'];
    $visible = $get_data['visible'];

    $marks_summary_table = GetSummaryMarksTableName($year_end, $level, $version);
    $query = "SELECT * FROM $marks_summary_table WHERE sid=$sid";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");

        exit();
    }
    $rec_student = $results->fetch();
    $student_name = $rec_student['name'];
    $student_tutor_initials = $rec_student['tutor'];
    $entry = "level$level" . "_avg";
    $student_level_average = $rec_student[$entry];
    $entry = "level$level" . "_credits";
    $student_level_credits = $rec_student[$entry];

    $plot_title = "Module box plots for $student_name for year $year_begin-$year_end_short. Level $level ";
    if ($version == 2) {
        $plot_title .= " (resit)";
    }

    $module_names_table = GetModuleNamesTableName($year_end, $level, $version);

    $query = "SELECT * FROM $module_names_table WHERE 1";
//echo("echo: $query<BR>");
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");

        exit();
    }
    $recs = $results->fetchAll();
    $iqr = array();

    $module_summary_names = array();
    $module_iqrs = array();
    $module_means = array();
    $module_marks = array();

    $module_index = 0;
    $sum = 0;
    $sum_credits = 0;
    $i = 1;
//print_r2($rec_student);
    foreach ($recs as $rec) {
        //print_r2($rec);
        $entry = "m$i";
        $mark = $rec_student[$entry];
        //echo("mark: $mark<BR>\n");
        if (is_numeric($mark)) {
            $iqr = array();
            $min = $rec['min'];
            $q25 = $rec['q25'];
            $q50 = $rec['q50'];
            $q75 = $rec['q75'];
            $max = $rec['max'];
            $credits = $rec['credits'];
            $iqr[] = floatval($min);
            $iqr[] = floatval($q25);
            $iqr[] = floatval($q50);
            $iqr[] = floatval($q75);
            $iqr[] = floatval($max);
            $module_iqrs[] = $iqr;

            $mean = floatval($rec['mean']);
            $module_means[] = [$module_index, $mean];

            $module_mark = floatval($mark);
            $module_marks[] = [$module_index, $module_mark];

            $sum += $module_mark*$credits;
            $sum_credits += $credits;

            $module_summary_names[] = $rec['summary_name'] . " [$credits]";

            $module_index++;
        }
        $i++;
    }
    $student_mean = $sum / $sum_credits;

    $graph_data = array('module_iqrs'=>$module_iqrs, 'module_means'=>$module_means, 'module_marks'=>$module_marks,
                        'module_summary_names'=>$module_summary_names, 'plot_title'=>$plot_title, 'student_mean'=>$student_mean);

    echo json_encode($graph_data);

}
exit;