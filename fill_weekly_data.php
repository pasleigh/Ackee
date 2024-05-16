<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot find this file to include: table_functions.php<BR>");
(@include_once("./student_data_functions.php")) OR die("Cannot find this file to include: student_data_functions.php<BR>");
(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");

set_time_limit(3600);
ini_set('memory_limit', '256M'); // need plenty of memory
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

;
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
        $school_short_name_lc =strtolower($school_short_name);
        $school_title = $recs['school_title'];
        echo("school $school_title<BR>");
        $module_prefix = $recs['default_module_prefix'];
        $running_title = $recs['running_title'];
        $running_subtitle = $recs['running_subtitle'];
        $year_session = $recs['year'];
        $year_begin = substr($year_session, 0, 4);
        $year_end = $year_begin + 1;
        $year_end_short = $year_end - 2000;
        $raw_data_issue = $recs['raw_data_issue'];
        $raw_data_version = $recs['raw_data_version'];

        $year_now = date("Y");

        $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
        $display_block .= "<p>Setting weekly attendance data of both students and modules of $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Create the sutudent list table
        $version = 1;
        $student_list_table = GetStudentListTableName($school_short_name, $year_session, $version);

        // create the student-module-list table
        $student_module_table = GetStudentModuleTableName($school_short_name, $year_session, $version);

        $mod_summary_table = GetModuleSummaryStatsTableName($school_short_name, $year_session);

        // Get the table name of the raw data
        //$display_block .= "<p>Student list table: $student_list_table</p>";
        $display_block .= "<p>Student-Module table: $student_module_table</p><BR>";
        $display_block .= "<p>Module summary table: $mod_summary_table</p><BR>";
        $display_block .= "<p>Step 7: Populate weekly attendance data</p>";


        $total_weeks = $term_1_num_weeks+$term_2_num_weeks+$term_3_num_weeks;

        for($i = 0 ; $i < $total_weeks ; $i++) {
            //$date1 = $weeks[$i]->format('Y-m-d H:i:s');
            //$date2 = $weeks[$i+1]->format('Y-m-d H:i:s');
            $date1 = $weeks_str[$i];
            $date2 = $weeks_str[$i+1];

            //$query = "SELECT COUNT(*) FROM $student_module_table ";
            //$results = $db->query($query);
            //var_dump($query);
            //var_dump($results->fetchAll());

            // Get unique list of student IDs
            $query = "SELECT id, sid, module_table FROM $student_module_table WHERE 1";//sid LIKE '2010%'";
            //var_dump($query);echo("<BR>");
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

            //var_dump($recs);
            foreach ($recs as $rec) {

                $id = $rec["id"];
                $sid = $rec["sid"];
                $module_table = $rec["module_table"];
                $raw_module_table = "raw_" . $module_table;
                //echo("id=$id  sid=$sid module_table=$module_table<BR>\n");
                // get all teh modules for thsi student
                $query = "SELECT * FROM $raw_module_table WHERE sid='$sid' AND date_time_start BETWEEN '$date1' AND '$date2'";

                try {
                    $results = $db->query($query);
                } catch (PDOException $ex) {
                    $this_function = __FUNCTION__;
                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                    echo(" Query = $query<BR> \n");
                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                    exit();
                }
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

                $num_present = $num_AA + $num_PB + $num_PR + $num_PS;
                $num_absent = $num_AD + $num_AR + $num_UA + $num_NR;
                $num_total = $num_present+$num_absent;
                if( $num_total == 0){
                    $attend_pc = -1.0;
                }else{
                    $attend_pc = round($num_present*100.0/$num_total,1);
                }
                // Write the attendance stats to the
                $query = "UPDATE $student_module_table SET w$i='$attend_pc' WHERE id='$id' ";
                // Check that the column exists
                //if ( $col_check_result = $db->query( "SHOW COLUMNS FROM $student_module_table LIKE 'w$i'" ) ) {
                    //if ( $col_check_result->rowCount() > 0 ) {
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
                //}

            }

            //=============== Now the module weekly stats =================
            // Get unique list of student IDs
            $query = "SELECT * FROM $mod_summary_table WHERE 1";
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

            foreach ($recs as $rec) {
                $module_code = $rec["module_code"];
                $module_table = $rec["module_table"];
                $raw_module_table = "raw_" . $module_table;

                // get all teh modules for thsi student
                $query = "SELECT * FROM $raw_module_table WHERE date_time_start BETWEEN '$date1' AND '$date2'";
                try {
                    $results = $db->query($query);
                } catch (PDOException $ex) {
                    $this_function = __FUNCTION__;
                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                    echo(" Query = $query<BR> \n");
                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                    exit();
                }
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

                $num_present = $num_AA + $num_PB + $num_PR + $num_PS;
                $num_absent = $num_AD + $num_AR + $num_UA + $num_NR;
                $num_total = $num_present+$num_absent;
                if( $num_total == 0){
                    $attend_pc = -1.0;
                }else{
                    $attend_pc = round($num_present*100.0/$num_total,1);
                }
                // Write the attendance stats to the
                $query = "UPDATE $mod_summary_table SET w$i='$attend_pc' WHERE module_code='$module_code' ";
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
        .jumbotron {
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
