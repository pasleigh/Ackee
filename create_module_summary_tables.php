<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot find this file to include: table_functions.php<BR>");

(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");

set_time_limit(3600);
/*
echo("<table border=0>");
foreach ($_REQUEST as $key=>$val )
{
  echo "<tr><td>".$key."</td><td>" .$val."</tr>";
}
echo("</table>");
*/

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
        $year_end = $year_begin+1;
        $year_end_short = $year_end-2000;
        $raw_data_issue = $recs['raw_data_issue'];
        $raw_data_version = $recs['raw_data_version'];

        $year_now = date("Y");

        $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
        $display_block .= "<p>Processing raw data of $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Get the table name of the raw data

        $raw_data_table =GetRawDataTableName($school_short_name, $year_session, $raw_data_issue, $raw_data_version);
        $display_block .= "<p>$raw_data_table</p>";
        $display_block .= "<p>Step 0: Created the module summary tables from raw data</p>";

        // Get unique list of name activities
        $query = "SELECT DISTINCT activity FROM $raw_data_table ORDER BY activity";
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
        $i = 0;
        foreach($recs as $rec) {
            //$date_o = $rec["date"];
            //$time_o = $rec["start_time"];
            $activity_name_o = $rec["activity"];
            $activity_name = str_replace( "\"", "",$activity_name_o );

            // Replace quoted string with non quoted
            $query = "UPDATE $raw_data_table SET activity='$activity_name' WHERE activity='$activity_name_o'";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }

            $display_block .= $activity_name . " = ";
            $all_activities[] = $activity_name;
            $pos = strpos($activity_name,"/");
            if($pos === FALSE){
                $module_name = $activity_name;
            }else{
                $module_name = substr($activity_name,0,$pos);
            }
            $all_modules[] = $module_name;
            $display_block .= $module_name;// . "<BR>";

            // Get the dates for this this activity to see if there is more than one
            $query = "SELECT DISTINCT date FROM $raw_data_table WHERE activity='$activity_name'";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $num_dates = $results->rowCount();
            $display_block .= "  num dates: " .$num_dates . "<BR>";

            // now find the times corresponding to this activity-date combination
            $date_recs = $results->fetchAll();
            foreach($date_recs as $date_rec){
                $date = $date_rec['date'];
                // Get the dates for this this activity to see if there is more than one
                $query = "SELECT DISTINCT start_time FROM $raw_data_table WHERE activity='$activity_name' AND date='$date'";
                try {
                    $results = $db->query($query);
                } catch (PDOException $ex) {
                    $this_function = __FUNCTION__;
                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                    echo(" Query = $query<BR> \n");
                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                    exit();
                }
                $num_times = $results->rowCount();
                $time_recs = $results->fetchAll();
                //$display_block .= "  num times: " .$num_times . "<BR>";
                foreach($time_recs as $time_rec) {
                    $start_time = $time_rec['start_time'];
                    $display_block .= $activity_name . " : " . $date . " : " . $start_time . "<BR>";
                    $all_activities_date_time[] = array($activity_name, $date,$start_time,$module_name);
                    //$all_activities_date_time[][1] = $date;
                    //$all_activities_date_time[][2] = $start_time;
                    //$all_activities_date_time[][3] = $module_name;
                }

            }

            //$i = $i + 1;
            //if($i > 100){break;}
        }
        //var_dump($all_modules);
        $display_block .= "========== MODULES ============== " . count($all_modules) . "<BR>";
        $all_modules = array_unique($all_modules);
        natsort($all_modules);
        $version = 1;
        foreach ($all_modules as $module_name) {
            $display_block .= $module_name . "<BR>";
            //echo( $module_name . "<BR>"); ob_flush();
            //exit();
            // Create the module table
            create_module_attend_activities_table_structure($db, $school_short_name, $year_session, $module_name, $version, $module_table_name);
            //echo("$module_table_name <BR>");
            truncate_table($db,$module_table_name);
        }

        foreach($all_activities_date_time as $activity_date_time_mod){
            //var_dump($activity_date_time_mod);
            //var_dump($all_activities_date_time);
            //exit();

            $activity_name = $activity_date_time_mod[0];
            $activity_date = $activity_date_time_mod[1];
            $activity_start_time = $activity_date_time_mod[2];
            $activity_module_code = $activity_date_time_mod[3];

            $module_table_name = GetModuleAttendTableName($school_short_name, $year_session, $activity_module_code, $version);

            // fill the table with the activities for this module
            // Find the activities that start with this module name
            $query = "SELECT * FROM $raw_data_table WHERE activity='$activity_name' AND date='$activity_date' AND start_time='$activity_start_time'";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $rec = $results->fetch();


            // Add this activity to the table
            //foreach($recs as $rec) {
                //print_r2($activity_rec);exit();
                $date = $rec["date"];
                $start_time = $rec["start_time"];
                $end_time = $rec["end_time"];
                if($raw_data_version == 2){
                    // Civil date format = 20160924
                    $day = substr($date,6,2);
                    $month = substr($date,4,2);
                    $year = substr($date,0,4);
                }else{
                    // Date format is 24/09/2016
                    $day = substr($date,0,2);
                    $month = substr($date,3,2);
                    $year = substr($date,6);
                }
                $date_php_start = "$day-$month-$year $start_time:00"; //'23-5-2016 23:15:23';
                $date_for_database_start = date ("Y-m-d H:i:s", strtotime($date_php_start));
                $date_php_end = "$day-$month-$year $end_time:00"; //'23-5-2016 23:15:23';
                $date_for_database_end = date ("Y-m-d H:i:s", strtotime($date_php_end));

                $query = "REPLACE INTO $module_table_name (activity, date_original, start_time_original, module_code, date_time_start, date_time_end) values('$activity_name', '$activity_date', '$activity_start_time', '$activity_module_code', '$date_for_database_start', '$date_for_database_end')";
                try {
                    $results = $db->query($query);
                } catch (PDOException $ex) {
                    $this_function = __FUNCTION__;
                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                    echo(" Query = $query<BR> \n");
                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                    exit();
                }
            //}
        }

        $display_block .= "</DIV>\n";


        //create_attend_activities_table_structure($db, $year, $school_short_name, $raw_data_issue, $module_summary_table_name);

        // get the name of the raw data table


        } else {
            echo("here: entry does not exist $query<BR>");
            $display_block .= "This year (<B>$year_begin</B>) has not been previously enterd.<BR>";
            $display_block .= "Select again or go to this <A href=\"./add_new_marksheets.php\">Add new mark sheets</A> link.<BR>&nbsp;<BR>\n";
    }

    /*
    $display_block .= "<div class='row'>";
    $display_block .= "<div class='col-xs-12'>";
    $display_block .= "<div class='jumbotron'>";
    $display_block .= "<h2>System Level admin functions</h2>";
    $display_block .= "<div class='row'>";
    $display_block .= "<div class='col-xs-6'>";
    $display_block .= "<A href='./upload_new_marksheets.php'  class='btn btn-primary btn-block'>Upload/edit mark sheets</A>\n";
    $display_block .= "<a href='./create_module_summary_tables.php'  class='btn btn-primary btn-block'>Create the module sumamry tables from raw data</a>\n";
    $display_block .= "<a href='./create_student_summary_tables.php'  class='btn btn-primary btn-block'>Create the student summary tables from raw data</a>\n";
    $display_block .= "</DIV>\n";
    $display_block .= "<div class='col-xs-6'>";
    $display_block .= "<a href='./todo.php'  class='btn btn-primary btn-block'>not functional yet</a>\n";
    $display_block .= "<a href='./toto.php'  class='btn btn-primary btn-block'>not functional yet</a>\n";
    $display_block .= "<a href='./toto.php'  class='btn btn-primary btn-block'>Load new raw data</a>\n";
    $display_block .= "</DIV>\n";
    $display_block .= "</DIV>\n";
    $display_block .= "</DIV>\n";
    $display_block .= "</DIV>\n";
    $display_block .= "</DIV>\n";
    */
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
