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
        $school_short_name_lc =strtolower($school_short_name);
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
        $display_block .= "<p>Updating the student list for Filtered data from the raw data of $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Get the sutudent list table name
        $student_list_table = GetStudentListTableName($school_short_name, $year_session, 1);

        // create the student-module-list table for filtered session data
        create_student_module_filtered_table_structure($db, $school_short_name, $year_session, 1, $student_module_filtered_table);
        truncate_table($db, $student_module_filtered_table);

        // create the student-module-list table for filtered session data - SMALL classes
        create_student_module_filtered_small_table_structure($db, $school_short_name, $year_session, 1, $student_module_filtered_small_table);
        truncate_table($db, $student_module_filtered_small_table);


        // Get the table name of the raw data
        $raw_data_table = GetRawDataTableName($school_short_name, $year_session, $raw_data_issue, $raw_data_version);
        $display_block .= "<p>Raw data table: $raw_data_table</p>";
        $display_block .= "<p>Student list table: $student_list_table</p>";
        $display_block .= "<p>Student-Module filtered table: $student_module_filtered_table</p><BR>";
        $display_block .= "<p>Student-Module filtered small-class table: $student_module_filtered_small_table</p><BR>";
        $display_block .= "<p>Step 9: Create the student list filtered tables from raw data</p>";

        // Loop through all of the module tables
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
        $mod_recs = $results->fetchAll();

        // Now find the distinct students on these modules
        // and start to populate the filter student_module tables
        foreach ($mod_recs as $mod_rec) {
            $module_table_name = $mod_rec[0];
            //$pos = strpos($module_table_name,"_",4)+1;
            $pos = strpos($module_table_name,"_",4+strlen($school_short_name_lc))+1;
            $pos2 = strpos($module_table_name,"_",$pos);
            $module_code = substr($module_table_name,$pos,$pos2-$pos);
            $module_code_uc = strtoupper($module_code);
            $display_block .= "module_code: $module_code<BR>";

            $query = "SELECT DISTINCT sid FROM $raw_data_table WHERE activity LIKE '$module_code_uc%'";

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
            $recs = $results->fetchAll();
            if ($num > 0) {
                // Add this list of students to the student-module table
                foreach($recs as $rec ){
                    $sid = $rec['sid'];
                    $query = "INSERT INTO $student_module_filtered_table (sid, module_code, module_table) VALUES ('$sid', '$module_code', '$module_table_name')";
                    try {
                        $results = $db->query($query);
                    } catch (PDOException $ex) {
                        $this_function = __FUNCTION__;
                        echo "An Error occured accessing the database in function: $this_function <BR>\n";
                        echo(" Query = $query<BR> \n");
                        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                        exit();
                    }
                    // And teh small group table
                    $query = "INSERT INTO $student_module_filtered_small_table (sid, module_code, module_table) VALUES ('$sid', '$module_code', '$module_table_name')";
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
