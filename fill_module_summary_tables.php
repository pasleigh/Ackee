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
        $year_begin = intval(substr($year_session,0,4));
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

        $display_block .= "<p>Step 2 : Populate the module tables from raw data</p>";

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
        foreach($recs as $rec) {

            // 2 - for each module loop through the activities and find all the entries for that activity
            //
            $module_table_name = $rec[0];

            // Get the list of activities
            $query = "SELECT * FROM $module_table_name WHERE 1";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__ ;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $activity_recs = $results->fetchAll();

            foreach($activity_recs as $activity_rec) {
                $activity_name = $activity_rec["activity"];
                $activity_date = $activity_rec["date_original"];
                $activity_start_time = $activity_rec["start_time_original"];

                // Search the raw file for this activity
                // Get the list of activities
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
                $num = $results->rowCount();
                $attendance_recs = $results->fetchAll();

                //  Add up the records
                $num_AA = 0;
                $num_AD = 0;
                $num_AR = 0;
                $num_NR = 0;
                $num_PB = 0;
                $num_PR = 0;
                $num_PS = 0;
                $num_UA = 0;
                foreach ($attendance_recs as $attendance_rec) {
                    $attend_code1 = $attendance_rec['attend_code1'];
                    if ($raw_data_version == 2) {// civil
                        $attend_code2 = $attendance_rec['attend_code2'];
                        //echo("Activity: $activity_name Date $activity_date<BR>");
                        //echo("Attend code 1 : $attend_code1<BR>");
                        //echo("Attend code 2 : $attend_code2<BR>");
                        if ($attend_code2 != "") {
                            $attend_code1 = $attend_code2;
                            //echo("Attend code 1 : $attend_code1<BR>");
                            //echo("Attend code 2 : $attend_code2<BR>");
                            //exit();
                        }
                    }
                    switch ($attend_code1) {
                        case "AA":
                            $num_AA++;
                            break;
                        case "AD":
                            $num_AD++;
                            break;
                        case "AR":
                            $num_AR++;
                            break;
                        case "NR":
                            $num_NR++;
                            break;
                        case "PB":
                            $num_PB++;
                            break;
                        case "PR":
                            $num_PR++;
                            break;
                        case "PS":
                            $num_PS++;
                            break;
                        case "UA":
                            $num_UA++;
                            break;
                    }
                }

                /*
                echo("num_AA = $num_AA, ");
                echo("num_PB = $num_PB, ");
                echo("num_PR = $num_PR, ");
                echo("num_PS = $num_PS <BR> ");
                echo("num_AD = $num_AD, ");
                echo("num_AR = $num_AR, ");
                echo("num_UA = $num_UA, ");
                echo("num_NR = $num_NR <BR> ");
                */
                $num_present = $num_AA + $num_PB + $num_PR + $num_PS;
                $num_absent = $num_AD + $num_AR + $num_UA + $num_NR;

                $total_num = $num_present + $num_absent;

                if($total_num > 0) {
                    $attendance = round($num_present / $total_num * 100.0,1); // 1dp
                }else{
                    $attendance = 0;
                }
                $flag_low_numbers = 0;
                if($total_num < $low_numbers_value){
                    $flag_low_numbers = 1;
                }
                $flag_low_attendance = 0;
                if($attendance < $low_attendance_value){
                    $flag_low_attendance = 1;
                }

                // Write the attendance stats to the
                $query = "UPDATE $module_table_name SET number='$num' , attend='$num_present', ";
                $query .= "num_AA='$num_AA', num_AD='$num_AD', num_AR='$num_AR', ";
                $query .= "num_NR='$num_NR', num_PB='$num_PB', num_PR='$num_PR', num_PS='$num_PS', num_UA='$num_UA', ";
                $query .= "attendance='$attendance', flag_low_numbers='$flag_low_numbers', flag_low_attendance='$flag_low_attendance' ";
                $query .= "WHERE activity='$activity_name' AND date_original='$activity_date' AND start_time_original='$activity_start_time'";
                //echo("$query <BR>");
/*
                $query = "REPLACE INTO $module_table_name (number, attend, num_AA, num_AD, num_AR, num_NR, num_PB, num_PR, num_PS, num_UA) values('$num', '$num_present', '$num_AA', '$num_AD', '$num_AR', '$num_NR', '$num_PB', '$num_PR', '$num_PS', '$num_UA') WHERE activity='$activity_name'";
*/
                //echo($query . "<BR>");exit(0);
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
            //if($count > 0 ){break;}
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
