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
        $year_end = $year_begin + 1;
        $year_end_short = $year_end - 2000;
        $raw_data_issue = $recs['raw_data_issue'];
        $raw_data_version = $recs['raw_data_version'];

        $year_now = date("Y");

        $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
        $display_block .= "<p>Making the student list from the raw data of $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Create the sutudent list table
        create_student_list_table_structure($db, $school_short_name, $year_session, 1, $student_list_table);
        truncate_table($db, $student_list_table);

        // create the student-module-list table
        create_student_module_table_structure($db, $school_short_name, $year_session, 1, $student_module_table);
        truncate_table($db, $student_module_table);

        // Get the table name of the raw data
        $raw_data_table = GetRawDataTableName($school_short_name, $year_session, $raw_data_issue, $raw_data_version);
        $display_block .= "<p>Raw data table: $raw_data_table</p>";
        $display_block .= "<p>Student list table: $student_list_table</p>";
        $display_block .= "<p>Student-Module table: $student_module_table</p><BR>";
        $display_block .= "<p>Step 5: Create the student list tables from raw data</p>";

        // Get unique list of student IDs
        $query = "SELECT DISTINCT sid FROM $raw_data_table ORDER BY student_family_name ";
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

        $i =0;
        foreach ($recs as $rec) {
            //$date_o = $rec["date"];
            //$time_o = $rec["start_time"];
            $sid = $rec["sid"];

            // get the student name and family name
            $query = "SELECT * FROM $raw_data_table WHERE sid='$sid' LIMIT 1";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $student_recs = $results->fetchAll();
            foreach ($student_recs as $student_rec) {
                if ($raw_data_version == 1) {// mech
                    $first_name = addslashes($student_rec['student_first_name']);
                } else { // civil
                    $first_name = addslashes($student_rec['student_name']);
                }
                $family_name = addslashes($student_rec['student_family_name']);
            }

            $display_block .= sprintf("%04d %08d : %s, %s<BR>",$i,$sid ,$family_name, $first_name);
            $i++;
            // Put these in the $student_list table
            $query = "INSERT INTO $student_list_table (sid, family_name, first_name) values('$sid',  '$family_name', '$first_name')";
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

        // Store these in the student_module_table
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

        // Now find the distinct students on these module
        foreach ($mod_recs as $mod_rec) {
            $module_table_name = $mod_rec[0];
            $pos = strpos($module_table_name,"_",4)+1;
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
                    $query = "INSERT INTO $student_module_table (sid, module_code, module_table) VALUES ('$sid', '$module_code', '$module_table_name')";
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
