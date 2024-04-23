<?php

(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot find this file to include: table_functions.php<BR>");
(@include_once("./student_data_functions.php")) OR die("Cannot find this file to include: student_data_functions.php<BR>");
(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");

//set_time_limit(3600);
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
        $display_block .= "<p>Setting the student level for the student list from the student-module list of $school_title $year_begin-$year_end_short, data issue ($raw_data_issue)</p>\n";
        //$display_block .= "<P><A href='admin_delete_year_level_version_entry_bs.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";

        // Create the sutudent list table
        $version = 1;
        $student_list_table = GetStudentListTableName($school_short_name, $year_session, $version);

        // create the student-module-list table
        $student_module_table = GetStudentModuleTableName($school_short_name, $year_session, $version);

        // Get the table name of the raw data
        $display_block .= "<p>Student list table: $student_list_table</p>";
        $display_block .= "<p>Student-Module table: $student_module_table</p><BR>";
        $display_block .= "<p>Step 6: Set the student levels</p>";

        // Get unique list of student IDs
        $query = "SELECT DISTINCT sid FROM $student_list_table";
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
            $sid = $rec["sid"];

            // get all teh modules for thsi student
            $query = "SELECT * FROM $student_module_table WHERE sid='$sid'";
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
            $module_list = array();
            foreach ($student_recs as $student_rec) {
                $module_list[] =  $student_rec['module_code'];
            }
            //echo("sid = $sid<BR>");
            $student_level = GetLevelFromModuleList($module_list);

            $query = "UPDATE $student_list_table SET level=$student_level WHERE sid='$sid'";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $i++;
            //if($i > 5 ){
            //    exit();
            //}
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
