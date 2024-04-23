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
        $year_begin = substr($year_session, 0, 4);
        $year_end = intval($year_begin) + 1;
        $year_end_short = $year_end - 2000;
        $raw_data_issue = $recs['raw_data_issue'];
        $raw_data_version = $recs['raw_data_version'];

        $year_now = date("Y");

        $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
        $display_block .= "<p>Splitting raw data into modules tables for $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Get the table name of the raw data

        $raw_data_table = GetRawDataTableName($school_short_name, $year_session, $raw_data_issue, $raw_data_version);
        $display_block .= "<p>$raw_data_table</p>";
        $display_block .= "<p>Step 4: Create and fill module raw tables</p>";

        // 1 - loop through the mod_civl / mod_mech tables
        // SELECT table_name FROM information_schema.tables WHERE table_name LIKE 'mod_$school_short_name_lc'
        $school_short_name_lc = strtolower($school_short_name);
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
        foreach ($recs as $rec) {

            // 2 - for each module loop through the activities and find all the entries for that activity
            //
            $module_table_name = $rec[0];

            // Get the module code
            $query = "SELECT module_code FROM $module_table_name WHERE 1";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $activity_recs = $results->fetchAll();
            $module_code = $activity_recs[0]['module_code'];
            $module_code_uc = strtoupper($module_code);

            // Create the module_raw table
            $version = 1;
            $delete = 1;
            create_module_raw_table_structure($db, $school_short_name, $year_session, $module_code, $version, $module_raw_table_name, $delete);

            // get from  the main raw table copying the module data into it
            $query = "SELECT * FROM $raw_data_table WHERE activity LIKE '$module_code_uc%'";

            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $num = $results->rowCount();
            $activity_recs = $results->fetchAll();

            foreach ($activity_recs as $activity_rec) {
                $activity_name = $activity_rec["activity"];
                $date_original = $activity_rec["date"];
                $start_time_original = $activity_rec["start_time"];
                $sid = $activity_rec["sid"];

                $family_name = $activity_rec["student_family_name"];
                if ($raw_data_version == 2) {// civil
                    $first_name = $activity_rec["student_name"];
                } else {//mech
                    $first_name = $activity_rec["student_first_name"];
                }
                $family_name = addslashes($family_name);
                $first_name = addslashes($first_name);

                $attend_code1 = $activity_rec['attend_code1'];
                if ($raw_data_version == 2) {// civil
                    $attend_code2 = $activity_rec['attend_code2'];
                    if ($attend_code2 != "") {
                        $attend_code1 = $attend_code2;
                    }
                }

                // reformat the dates
                $date = $activity_rec["date"];
                $start_time = $activity_rec["start_time"];
                $end_time = $activity_rec["end_time"];
                if ($raw_data_version == 2) {
                    // Civil date format = 20160924
                    $day = substr($date, 6, 2);
                    $month = substr($date, 4, 2);
                    $year = substr($date, 0, 4);
                } else {
                    // Date format is 24/09/2016
                    $day = substr($date, 0, 2);
                    $month = substr($date, 3, 2);
                    $year = substr($date, 6);
                }
                $date_php_start = "$day-$month-$year $start_time:00"; //'23-5-2016 23:15:23';
                $date_for_database_start = date("Y-m-d H:i:s", strtotime($date_php_start));
                $date_php_end = "$day-$month-$year $end_time:00"; //'23-5-2016 23:15:23';
                $date_for_database_end = date("Y-m-d H:i:s", strtotime($date_php_end));

                $query = "INSERT INTO $module_raw_table_name (activity, date_original, start_time_original, module_code, date_time_start, date_time_end, sid, family_name, first_name, attend_code1) values('$activity_name', '$date_original', '$start_time_original', '$module_code', '$date_for_database_start', '$date_for_database_end', '$sid', '$family_name', '$first_name', '$attend_code1')";

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


        }

        $display_block .= "</DIV>\n";


        //create_attend_activities_table_structure($db, $year, $school_short_name, $raw_data_issue, $module_summary_table_name);

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
    <?php echo($ackee_bookstrap_links) ?>

    <style type="text/css">
        .jumbotron {
            margin-top: 10px;
            padding-top: 10px;
            background-color: goldenrod;
            border-width: 1px;
        }
    </style>
</head>
<body>
<?php echo($ackee_nav_bar) ?>

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
