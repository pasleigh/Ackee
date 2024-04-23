<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot find this file to include: table_functions.php<BR>");
(@include_once("./iqr.php")) OR die("Cannot find this file to include: iqr.php<BR>");
(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");

set_time_limit(3600); // seconds
/*
echo("<table border=0>");
foreach ($_REQUEST as $key=>$val )
{
  echo "<tr><td>".$key."</td><td>" .$val."</tr>";
}
echo("</table>");
*/
function GetModuleIQR($db,$module_attend_table,&$module_student_number, &$module_mean_attend, &$number_of_activities){
    // Calcs the module IQR stats, returns an array
    /*
    $min = $iqr[0];
    $q25 = $iqr[1];
    $q50 = $iqr[2];
    $q75 = $iqr[3];
    $max = $iqr[4];
    */
    $query = "SELECT * FROM $module_attend_table WHERE 1";
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
    $module_student_number = 0;
    $number_of_activities = 0;
    $attendance_record = Array();
    foreach($recs as $rec){
        $number = floatval($rec["number"]);
        $attend = floatval($rec["attend"]);
        if($number > 0.0 && $attend > 0.0 ) {
            $attendance_record[] = $attend / $number * 100.0;
            if($number > $module_student_number){
                $module_student_number = $number;
            }
            $number_of_activities++;
        }
    }

    if($number_of_activities > 0){
        $module_mean_attend = array_sum($attendance_record) / count($attendance_record);
        return BoxPlot5Stats($attendance_record);
    }else{
        $module_mean_attend = 0.0;
        return array(0,0,0,0,0);
    }
}

$display_block = "";

// Check the admin level - done in setup-params

$display_block .= "<h3 style='color: darkgoldenrod'>$access_phrase</h3>\n";

if ($access) {
    if (array_key_exists('pm_id', $_REQUEST) == false) {
        $_REQUEST['pm_id'] = null;
    }
    $pm_id = $_REQUEST['pm_id'];

    if (array_key_exists('op', $_POST) == false) {
        $_POST['op'] = null;
    }
    $POST_OP = $_POST['op'];


    //if ($POST_OP == "select") {
    // Get the data set that we are working on
    $query = "SELECT * FROM $definition WHERE id=$pm_id";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }

    $recs = $results->fetch();

    $entry_exists = $results->rowCount() > 0;
    if ($entry_exists) { // exists

        $id = $recs['id'];
        $school_short_name = $recs['school_short_name'];
        $school_title = $recs['school_title'];
        echo("school $school_title<BR>");
        $module_prefix = $recs['default_module_prefix'];
        $running_title = $recs['running_title'];
        $running_subtitle = $recs['running_subtitle'];
        $year_session = $recs['year'];
        $year_begin = substr($year_session,0,4);
        $year_end = intval($year_begin)+1;
        $year_end_short = $year_end-2000;
        $raw_data_issue = $recs['raw_data_issue'];
        $raw_data_version = $recs['raw_data_version'];

        $year_now = date("Y");

        $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
        $display_block .= "<p>Processing module summary data of $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Get the table name of the raw data

        //$raw_data_table =GetRawDataTableName($school_short_name, $year_session, $raw_data_issue, $raw_data_version);
        $display_block .= "<p>Step 3: Populate the module statistics tables</p>";


        // 1 - loop through the mod_civl / mod_mech tables
        // SELECT table_name FROM information_schema.tables WHERE table_name LIKE 'mod_$school_short_name_lc'

        // Create the module summary table
        $school_short_name_lc = strtolower($school_short_name);
        //$mod_summary_table_name = "summary_$school_short_name_lc" . "_$year_session" ;//. "_i$raw_data_issue" . "_v$raw_data_version";
        $mod_summary_table_name = GetModuleSummaryStatsTableName($school_short_name, $year_session);
        $delete = 1;
        create_module_stats_table_structure($db, $mod_summary_table_name, $delete);
        truncate_table($db,$mod_summary_table_name);

        $query = "SHOW TABLES LIKE 'mod_$school_short_name_lc%'";

        $display_block .= $query . "<BR>";
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


        $count = 1;
        foreach($recs as $rec) {

            // create the summary table
            // 2 - for each module loop through the activities and find all the entries for that activity
            //
            $module_table_name = $rec[0];
            $display_block .= $module_table_name  . " - " . $rec[0]. "<BR>";
            //`school_short_name` varchar(10) NOT NULL,
            //`module_table` varchar(256) NOT NULL,
            //`module_code` varchar(256) NOT NULL,
            //`module_prefix` varchar(5) NOT NULL,
            //`level` INTEGER NOT NULL,
            $pos = strpos($module_table_name,"_",4)+1;
            $pos2 = strpos($module_table_name,"_",$pos)+1;
            $module_code = substr($module_table_name,$pos,$pos2-$pos-1);
            $module_level = substr($module_table_name,$pos+4,1);
            if(is_numeric($module_level)){
                $module_level = intval($module_level);
            }else{
                $module_level = 0;
            }

            $display_block .= "$module_table_name<BR>";

            // Get the atttendance records

            $iqr = GetModuleIQR($db,$module_table_name,$module_student_number, $module_mean_attend, $number_of_activities);
            $min = $iqr[0];
            $q25 = $iqr[1];
            $q50 = $iqr[2];
            $q75 = $iqr[3];
            $max = $iqr[4];
            //var_dump($iqr);

            $query = "REPLACE INTO $mod_summary_table_name ( module_code, school_short_name, module_table, module_prefix, level, number_in_class, number_of_activities,  min, max, q25, q50, q75, mean) ";
            $query .= " values('$module_code', '$school_short_name_lc' , '$module_table_name','$module_prefix', '$module_level', '$module_student_number', '$number_of_activities', '$min' , '$max', '$q25', '$q50', '$q75', '$module_mean_attend' )";
            //echo("<BR>$query<BR>");

            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }

        }

        $display_block .= "</DIV>\n";


        //create_attend_activities_table_structure($db, $year_session, $school_short_name, $raw_data_issue, $module_summary_table_name);

        // get the name of the raw data table


        } else {
            echo("here: entry does not exist $query<BR>");
            $display_block .= "This year (<B>$year_begin</B>) has not been previously enterd.<BR>";
            $display_block .= "Select again or go to this <A href=\"./add_new_marksheets.php\">Add new mark sheets</A> link.<BR>&nbsp;<BR>\n";
    }


}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing : Admin</title>
    <?php echo($ackee_bookstrap_links)?>

    <style type="text/css">
        .jumbotron{
            margin-top: 10px;
            padding-top: 10px;
            background-color: goldenrod;
            border-width: 1px;
        }
    </style>
</head>
<body>
<?php echo($ackee_nav_bar)?>

<div class="container">

    <div id="content">
        <?php echo $display_block; ?>
    </div><!-- /content -->

</div>
<footer class="footer">
    <div class="container">
        <p class="text-muted">If you have any questions, contact Andrew Sleigh: P.A.Sleigh@leeds.ac.uk</p>
    </div>
</footer>


</BODY>
</HTML>
