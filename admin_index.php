<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");

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

$display_block .= "<h2 style='font-size: x-small; color: darkgoldenrod'>$access_phrase</h2>\n";

if ($access) {
    if (array_key_exists('pm_id', $_REQUEST) == false) {
        $_REQUEST['pm_id'] = null;
    }
    $pm_id = $_REQUEST['pm_id'];

    if (array_key_exists('op', $_POST) == false) {
        $_POST['op'] = null;
    }
    $POST_OP = $_POST['op'];

    // Get a list of all years of data in the database
    $query = "SELECT * FROM $marks_index WHERE 1 ORDER BY year_begin DESC";
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
        $year_begin = $get_data['year_begin'];
        $year_end = $get_data['year_end'];
        $year_end_short = $year_end - 2000;
        $got_summary_table = $get_data['got_summary_table'];
        $got_module_table = $get_data['got_modules_table'];
        $version = $get_data['version'];
        $level = $get_data['level'];
        $visible = $get_data['visible'];
        $year_list_desc = "$year_begin-$year_end_short Level $level";
        if ($version == 2){
            $year_list_desc .= " (resit)";
        }
        $pm_id_s = $get_data['id'];
        if ($pm_id_s == $pm_id) {
            $selected = "selected";
        } else {
            $selected = "";
        }
        $year_list_options .= "<option $selected value='$pm_id_s'>$year_list_desc</option>\n";
    }

// Display the dropdowns to select the module and year
    $display_block .= "<h1>Select the data you want to view/modify ...</H1>\n";

    $display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">\n";

    $display_block .= "<P>Mark years currently in the database: \n<select name=\"pm_id\" >\n";
    $display_block .= $year_list_options;
    $display_block .= "</select>\n</P>";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"select\">\n";
    //$display_block .= "<input type='hidden' name='pm_id' value='$pm_id_s'>\n";

    $display_block .= "<p><input type=\"submit\" id=\"submitbutton\" name=\"submit\" value=\"View the selected data\"></p>\n";

    $display_block .= "</form>\n";

    if ($POST_OP == "select") {
        $query = "SELECT * FROM $marks_index WHERE id='$pm_id'";
        //echo("echo: $query<BR>");
        try {
            $results = $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");

            exit();
        }


        $entry_exists = $results->rowCount() > 0;
        if ($entry_exists) { // exists
            $recs = $results->fetch();
            $year_begin = $recs['year_begin'];
            $level = $recs['level'];
            $version = $recs['version'];
            $year_end_short = $year_begin + 1 - 2000;
            $year_now = date("Y");

            $resit_text = "";
            if($version == 2){
                $resit_text = ", resit";
            }

            $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
            $display_block .= "<p>Editing/viewing of the data for year $year_begin-$year_end_short, level $level $resit_text will go here</p>\n";
            $display_block .= "<P><A href='admin_delete_year_level_version_entry.php?pm_id=$pm_id'>Delete the entry for this year</A>. Are you sure as there is no going back!</P><BR>\n";
            $display_block .= "</DIV>\n";

        } else {
            echo("here: entry does not exist $query<BR>");
            $display_block .= "This year (<B>$year_begin</B>) has not been previously enterd.<BR>";
            $display_block .= "Select again or go to this <A href=\"./add_new_marksheets.php\">Add new mark sheets</A> link.<BR>&nbsp;<BR>\n";
        }
    }// End of POST select


    $display_block .= "<BR><BR>";
    $display_block .= "<DIV style='border-style: solid; border-width: 3px; border-color: maroon; padding: 5px; background-color: Khaki' >\n";
    $display_block .= "<h1>System Level admin functions</h1>";
    $display_block .= "<A href=\"./upload_new_marksheets.php\">Upload/edit mark sheets</A><BR>\n";
    $display_block .= "<BR><a href=\"./edit_module_visibility.php\">Edit which year's marks are visible to staff and studnets</a><br>\n";
    $display_block .= "<BR><a href=\"./change_school_definition.php\">Edit the School name</a><br>\n";
    $display_block .= "<BR><a href=\"./edit_staff_table.php\">Edit staff table</a><br>\n";
    $display_block .= "<BR><a href=\"./match_tutor_initials.php\">Match tutor initials to tutors</a><br>\n";

    $display_block .= "</DIV><BR>\n";

}
?>
<!DOCTYPE html>
<HTML>
<?php
(@include_once("./header.php")) OR die("Cannot find this file to include: header.php<BR>");
?>
<div id="wrapper">
    <div id="header">
        <span class="header"><img src="./images/chart_icon_negate_36.png" height=30px
                                  align="middle"><?php echo(" $running_title - $running_subtitle") ?></span>
    </div><!-- /header -->

    <div id="content">
        <?php echo $display_block; ?>
    </div><!-- /content -->

    <div id="footer">
        <P class="footer_link">Go to the
            <a href="./index.php">index page</a>.
        </P>
    </div><!-- /footer -->

</div><!-- /wrapper -->

</BODY>
</HTML>
