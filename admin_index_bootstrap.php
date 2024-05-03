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
//    echo("<BR><BR><BR><BR>pm_id = $pm_id<BR>");
//    echo("<pre>");
    if (array_key_exists('pm_id', $_REQUEST) == false) {
        $_REQUEST['pm_id'] = null;
    }
    $pm_id = $_REQUEST['pm_id'];
//echo("_REQUEST");
    //var_dump($_REQUEST);

    if (array_key_exists('pm_id', $_COOKIE) == false) {
        $_COOKIE['pm_id'] = null;
    }
    $pm_id = $_COOKIE['pm_id'];

//echo("_COOKIE");
//var_dump($_COOKIE);

//echo("</pre>");

    if (array_key_exists('op', $_POST) == false) {
        $_POST['op'] = null;
    }
    $POST_OP = $_POST['op'];

    // Get a list of all years of data in the database
    $query = "SELECT * FROM $definition WHERE 1 ORDER BY id";
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
    foreach ($results_data as $get_data) {
        $id = $get_data['id'];
        $school_short_name = $get_data['school_short_name'];
        $school_title = $get_data['school_title'];
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
        if ($pm_id == $id) {
            $selected = "selected";
        } else {
            $selected = "";
        }
        $year_list_options .= "<option $selected value='$id'>$year_list_desc</option>\n";
    }
    unset($results_data);

// Display the dropdowns to select the module and year
    $display_block .= "<h1>Select the raw data you want to process or enter new raw data ...</H1>\n";

    $display_block .= "<form id='selectschool' method=\"post\" action=\"$_SERVER[PHP_SELF]\">\n";

    //$display_block .= "<P>Mark years currently in the database: \n";
    $display_block .= "<div class='row'>\n";
    $display_block .= "    <div class='col-sm-6'>\n";
    $display_block .= "<label for='pm_id'>Select from the year list:</label>";
    $display_block .= "<select class='form-control' name='pm_id' id='pm_id'>\n";
    $display_block .= "<option selected disabled>Choose a raw attendance data set</option>\n";
    $display_block .= $year_list_options;
    $display_block .= "</select>\n";
    $display_block .= "     </div>";
    $display_block .= "    <div class='col-sm-6'></div>\n";
    $display_block .= "</div></p>\n";

    $display_block .= "<div class='row'>\n";
    $display_block .= "    <div class='col-sm-6'>\n";
    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"select\">\n";
    //$display_block .= "<input type='hidden' name='pm_id' value='$pm_id_s'>\n";

    $display_block .= "<p><input type='submit' class=\"btn btn-success btn-block\" id=\"submitbutton\" name=\"submit\" value=\"View processing functions for selected raw data\"></p>\n";
    $display_block .= "     </div>";
    $display_block .= "    <div class='col-sm-6'><a href='load_raw_data_csv.php' class='btn btn-info btn-block' role='button'>Load NEW raw data</a></div>\n";
    $display_block .= "</div>";
    $display_block .= "</form>\n";

    if ($POST_OP == "select" && $pm_id != null) {
        $display_block .= "<div id='process_raw_data_block' class='row'>";
        $display_block .= "<div class='col-xs-12'>";
        $display_block .= "<div class='jumbotron'>";
        $display_block .= "<h2>Function sequence required to process raw data</h2>";

        $display_block .= "<div class='row'>";
        $display_block .= "<div class='col-xs-6'>";
        $display_block .= "<A href='./csv2mysql.php'  class='btn btn-primary btn-block'>-1 - Upload/edit mark sheets</A>\n";
        $display_block .= "<a href='./create_module_summary_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>0 - Create the module tables from raw data</a>\n";
        $display_block .= "<a href='./create_and_fill_universal_raw_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>1 - Create and fill universal raw table</a>\n";
        $display_block .= "<a href='./fill_module_summary_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>2 - Populate the module tables from raw data</a>\n";
        $display_block .= "<a href='./fill_module_stats_summary_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>3 - Populate the module statistics tables </a>\n";
        $display_block .= "<a href='./create_and_fill_module_raw_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>4 - Create and fill module raw tables</a>\n";
        $display_block .= "<a href='./create_student_list_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>5 - Create the student list tables from raw data</a>\n";
        $display_block .= "<a href='./create_level_for_student_list_tables.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>6 - Set the student levels</a>\n";
        $display_block .= "<a href='./fill_weekly_data.php?pm_id=$pm_id'  class='btn btn-primary btn-block'>7 - Populate the weekly attendance data</a>\n";
        $display_block .= "</DIV>\n";
        $display_block .= "<div class='col-xs-6'>";
        $display_block .= "<a href='./edit_filter_values.php?pm_id=$pm_id'  class='btn btn-info btn-block'>8.1 - Edit filter parameters</a>\n";
        $display_block .= "<a href='./fill_module_stats_summary_filtered_tables.php?pm_id=$pm_id'  class='btn btn-info btn-block'>8.2 - Populate the <em>filtered</em> module statistics tables </a>\n";
        $display_block .= "<a href='./create_student_list_filtered_tables.php?pm_id=$pm_id'  class='btn btn-info btn-block'>9 - Create the <em>filtered</em> student list tables from raw data</a>\n";
        $display_block .= "<a href='./fill_filtered_weekly_data.php?pm_id=$pm_id'  class='btn btn-info btn-block'>10 - Populate the  <em>filtered</em> weekly attendance data</a>\n";
        //$display_block .= "<a href='./todo.php'  class='btn btn-info btn-block'>not functional yet</a>\n";
        $display_block .= "</DIV>\n";
        $display_block .= "</DIV>\n";
        $display_block .= "<br>";

        //$display_block .= "<div class='row'>";
        //$display_block .= "<div class='col-xs-6'>";
        //$display_block .= "<a href='./edit_filter_values.php?pm_id=$pm_id'  class='btn btn-info btn-block'>Edit filter parameters</a>\n";
        //$display_block .= "</DIV>\n";
        //$display_block .= "<div class='col-xs-6'>";
        //$display_block .= "</DIV>\n";
        //$display_block .= "</DIV>\n";
        //$display_block .= "<br>";

        $display_block .= "<div class='row'>";
        $display_block .= "<div class='col-xs-12'>";
        $display_block .= "<a href='./delete_module_tables.php?pm_id=$pm_id'  class='btn btn-warning btn-block'>DELETE ALL DATA  - DO NOT DO THIS UNLESS YOUARE RESETTING THE SYSTEM. There is NO second chance</a>\n";
        $display_block .= "</DIV>\n";
        $display_block .= "</DIV>\n";


        $display_block .= "</DIV>\n";
        $display_block .= "</DIV>\n";

        $display_block .= "</DIV>\n";

    }// End of POST select

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
<script>
$(document).ready(function(){
    $('#selectschool').submit(function() {
        var pm_id = $("#pm_id option:selected").val();
        // Set the pm_id as a cookie. Set for 7 days
        var days = 7; var seconds = days*24*60*60;
        docCookies.setItem("pm_id", pm_id, seconds);
        return true; // return false to cancel form action
    });
    $('#pm_id').on('change', function(){
        $('#process_raw_data_block').hide()
    })
})
</script>
</BODY>
</HTML>
