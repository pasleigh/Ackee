<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");

(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");
(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

$display_block = "";
//$display_block .= "<h2 style='font-size: x-small; color: darkgoldenrod'>$access_phrase</h2>\n";

if (true) {
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
    $display_block .= "<h1>Select the data you want to view ...</H1>\n";

    //$display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">\n";

    $display_block .= "<P>Mark years currently in the database: \n";
    $display_block .= "<select name=\"pm_id\"  id='pm_id' onchange='fetch_year_select(this.value);'>\n";
    $display_block .= "<option selected disabled>Choose a year</option>\n";
    $display_block .= $year_list_options;
    $display_block .= "</select>\n</P>";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"select\">\n";
    //$display_block .= "<input type='hidden' name='pm_id' value='$pm_id_s'>\n";

    //$display_block .= "<p><input type=\"submit\" id=\"submitbutton\" name=\"submit\" value=\"View the selected data\"></p>\n";

    //$display_block .= "</form>\n";


    $display_block .= "<BR>\n";
    $display_block .= "<div id='container' style='height: 580px; border-style: solid; border-width: 1px; border-color: black; padding: 5px; display: none;'></div>\n";


}
//----------------------------------------------->
$display_block .= "<BR><BR>\n";
$display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: darkgreen; padding: 5px; background-color: white' >\n";

$display_block .= "<p>Some demo examples</p>\n";
$display_block .= "<A href='./highchart_eg01.php'>Highchart boxplot example<a><BR><BR>\n";

$display_block .= "<A href='./highchart_eg02.php'>Responsive page eg<a><BR><BR>\n";

$display_block .= "<A href='./student.php'>Show individual student's results<a><BR>\n";
$display_block .= "<A href='./admin_index.php'>Admin pages<a><BR><BR>\n";
$display_block .= "</div>\n";


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

<!--Body content-->
<script type="text/javascript">

    function fetch_year_select(val) {

        pm_id = document.getElementById("pm_id").value;
        //alert("pm_id=" + pm_id);
        //alert("val=" + val);
        document.getElementById("container").style.display = 'block';

        $.ajax({
            type: 'post',
            url: 'fetch_year_chart_data.php',
            data: {
                get_pm_id: pm_id
            },
            dataType: "json",
            success: function (mydata) {
                //document.getElementById("student_select").innerHTML=response;
                //alert('you changed');
                //alert(mydata.plot_title);
                //alert(mydata);

                my_boxplots.series[0].data = mydata.module_iqrs;

                // spot data (means)
                my_boxplots.series[1].data = mydata.module_means;

                my_boxplots.xAxis.categories = mydata.module_summary_names;
                my_boxplots.title.text = mydata.plot_title;

                chart = new Highcharts.Chart(my_boxplots);

            }
        });
    }

</script>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script src="./year_box_charts.js"></script>

<script>
//    // Chart data
//    my_boxplots.series[0].data = <?php //echo json_encode($module_iqrs) ?>//;
//    /*
//    my_boxplots.series[0].data = [
//        [5, 10, 40, 70, 91],
//        [6, 20, 45, 60, 91],
//        [10, 25, 50, 60, 81],
//        [15, 30, 40, 70, 91],
//        [2, 24, 30, 60, 91]
//    ];
//    */
//
//    // spot data (means)
//    my_boxplots.series[1].data = <?php //echo json_encode($module_means) ?>//;
//    //my_boxplots.series[1].data = [ [0, 40], [4, 75] ];
//
//    my_boxplots.xAxis.categories = <?php //echo json_encode($module_summary_names) ?>//;
//    my_boxplots.title.text = <?php //echo json_encode($plot_title) ?>//;
//
//    chart = new Highcharts.Chart(my_boxplots);
</script>

</BODY>
</HTML>


