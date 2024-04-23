<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");

(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");
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

    if (array_key_exists('low_attendance', $_POST) == false) {
        $_POST['low_attendance'] = null;
    }
    $post_low_attendance_value = $_POST['low_attendance'];

    if (array_key_exists('low_numbers', $_POST) == false) {
        $_POST['low_numbers'] = null;
    }
    $post_low_numbers_value = $_POST['low_numbers'];

    if (array_key_exists('post_pm_id', $_REQUEST) == false) {
        $_REQUEST['post_pm_id'] = null;
    }
    $post_pm_id = $_REQUEST['post_pm_id'];
    if($post_pm_id != null){
        $pm_id = $post_pm_id;
    }
    //echo("pm_id = $pm_id<BR>");
    //echo("post_low_attendance_value = $post_low_attendance_value<BR>");
    //echo("post_low_numbers_value = $post_low_numbers_value<BR>");
    //echo("OP = $POST_OP<BR>");
    //var_dump($_POST);

    // Get a list of all years of data in the database
    $query = "SELECT * FROM $definition WHERE id='$pm_id'";
    //echo("$query<BR>");
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }

    $year_list_options = "";
    $results_data = $results->fetchAll();
    $selected = "";
    $pm_id_s = "";
    //var_dump($results_data);
    foreach ($results_data as $get_data) {
        $id = $get_data['id'];
        $school_short_name = $get_data['school_short_name'];
        $school_title = $get_data['school_title'];
        //echo("SChool name $school_short_name $school_title<BR>");
        $module_prefix = $get_data['default_module_prefix'];
        $running_title = $get_data['running_title'];
        $running_subtitle = $get_data['running_subtitle'];
        $year = $get_data['year'];
        $year_begin = substr($year,0,4);
        $year_end = intval($year_begin)+1;
        $year_end_short = $year_end-2000;
        $raw_data_issue = $get_data['raw_data_issue'];
        $raw_data_version = $get_data['raw_data_version'];
        $year_list_desc = "$school_title $year_begin-$year_end_short ($raw_data_issue)";
    }

    //echo("Low attendance value $low_attendance_value<BR>");
    //echo("Low numbers value $low_numbers_value<BR>");
    if ($POST_OP == "submit_filters") {

        $display_block .= "Putting these in the database for $school_title.<BR>";
        $display_block .= "Low attendance: $post_low_attendance_value<BR>\n";
        $display_block .= "Low numbers: $post_low_numbers_value<BR>\n";
        $query = "UPDATE $setup_params SET low_attendance_value='$post_low_attendance_value', low_numbers_value='$post_low_numbers_value' WHERE id='$post_pm_id' ";
        echo("$query <BR>");
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

    if($post_low_attendance_value == null) {
        $post_low_attendance_value = $low_attendance_value[1];
    }
    if($post_low_numbers_value == null) {
        $post_low_numbers_value = $low_numbers_value[1];
    }
// Display the dropdowns to select the module and year
    $display_block .= "<h1>Edit the filter values for $school_title</H1>\n";

    $display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">\n";

    //$display_block .= "<P>Mark years currently in the database: \n";
    $display_block .= "<div class='row'>\n";
    $display_block .= "    <div class='col-sm-6'>\n";
    $display_block .= "<div class='form-group'>";
    $display_block .= "<label for='low_attendance'>Ignore values below this attendance %:</label>";
    $display_block .= "<input type='text' class='form-control' value='$post_low_attendance_value' name='low_attendance' id='low_attendance'>";
    $display_block .= "</div>";
    $display_block .= "<div class='form-group'>";
    $display_block .= "<label for='low_numbers'>Ignore values below this class size:</label>";
    $display_block .= "<input type='text' class='form-control' value='$post_low_numbers_value' name='low_numbers' id='low_numbers'>";
    $display_block .= "</div>";
    $display_block .= "</div>";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"submit_filters\">\n";
    $display_block .= "\n<input type=\"hidden\" name=\"post_pm_id\" value=\"$pm_id\">\n";
    //$display_block .= "<input type='hidden' name='pm_id' value='$pm_id_s'>\n";

    $display_block .= "<p><input type='submit' class=\"btn btn-success btn-block\" id=\"submitbutton\" name=\"submit\" value=\"Submit these filter values\"></p>\n";
    $display_block .= "</div>";
    $display_block .= "</div>";
    $display_block .= "</form>\n";



}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing : Admin</title>
    <?php echo($ackee_bookstrap_links)?>

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
