<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");

(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");
(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

$display_block = "";
//$display_block .= "<h2 style='font-size: x-small; color: darkgoldenrod'>$access_phrase</h2>\n";

// Display the dropdowns to select the module and year

$welcome_phrase = "Hello $this_fullname ($this_username)";
$display_block .= "<h4 style='color: darkgoldenrod'>$welcome_phrase</h4>\n";

$display_block .= "<h1>Show marks and module boxplots for a particular student</H1>\n";

if (true) {
    if (array_key_exists('pm_id', $_REQUEST) == false) {
        $_REQUEST['pm_id'] = null;
    }
    $pm_id = $_REQUEST['pm_id'];

    if (array_key_exists('sid', $_REQUEST) == false) {
        $_REQUEST['sid'] = null;
    }
    $sid = $_REQUEST['sid'];

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
    $selected = "selected";
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
        if($selected == "selected"){$selected = "";}
    }


    //$display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">\n";

    //$display_block .= "<P>Mark years currently in the database: \n";
    $display_block .= "<div class='row'>\n";
    $display_block .= "    <div class='col-sm-6'>\n";
    //$display_block .= "<div style='padding-bottom: 10px'>\n";
    $display_block .= "<label for='pm_id'>Select from the year list:</label>";
    $display_block .= "<select class='form-control' name='pm_id' id='pm_id' onchange='fetch_select(this.value);'>\n";
    $display_block .= "<option selected disabled>Choose a year</option>\n";
    $display_block .= $year_list_options;
    $display_block .= "</select>\n";
    $display_block .= "    </div>";

    //$display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"selected_year\">\n";

    //$display_block .= "<input type='hidden' name='pm_id' value='$pm_id'>\n";

    $display_block .= "    <div class='col-sm-6'>\n";
    $display_block .= "<div id='student_select_div' style='display: none;'>\n";
    $display_block .= "<label for='student_select'>Select a student from this year:</label>";
    $display_block .= "<select  class='form-control' name='sid' id='student_select'  onchange='fetch_sid_select(this.value);'></select>\n";
    $display_block .= "</div>\n";
    $display_block .= "    </div>\n";

    $display_block .= "</div>\n";

    //$display_block .= "<p><input type=\"submit\" id=\"submitbutton\" name=\"submit\" value=\"View the selected data\"></p>\n";

    //$display_block .= "</form>\n";
    //$display_block .= "<BR>\n";
    //(@include_once("./student_data.php")) OR die("Cannot find this file to include: student_data.php<BR>");
    $display_block .= "<div class='row'>\n";
    $display_block .= "    <div class='col-sm-12'>\n";
    $display_block .= "<div id='container' style='height: 580px; border-style: solid; margin-top: 10px; margin-bottom: 10px; border-width: 1px; border-color: black; padding: 5px; display: none;'></div>\n";
    $display_block .= "    </div>";
    $display_block .= "</div>";


}
////----------------------------------------------->
//$display_block .= "<BR><BR>\n";
//$display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: darkgreen; padding: 5px; background-color: white' >\n";
//$display_block .= "<p>Some demo examples</p>\n";
//$display_block .= "<A href='./highchart_eg01.php'>Highchart boxplot example<a><BR><BR>\n";
//
//$display_block .= "<A href='./highchart_eg02.php'>Responsive page eg<a><BR><BR>\n";
//
//$display_block .= "<A href='./student.php'>Show individual student's results<a><BR>\n";
//$display_block .= "<A href='./admin_index.php'>Admin pages<a><BR><BR>\n";
//$display_block .= "</div>\n";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing : Student performance</title>
    <?php echo($ackee_bookstrap_links)?>
</head>
<body>
<nav id="myNavbar" class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <A class="navbar-brand" href="./index.php">AcKee</A>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Module Charts <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./module_summary_bootstrap.php">Summary Charts</a></li>
                        <li><a href="./module_detail_bootstrap.php">Detail Charts</a></li>
                    </ul>
                </li>
                <li class="dropdown active">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Student Charts <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./student_detail_bootstrap.php">Detailed summary</a></li>
                        <li><a href="./student_weekly_bootstrap.php">Weekly Summary</a></li>


                    </ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">>
                <li><a href="./admin_index_bootstrap.php" target="_top"><span class="glyphicon glyphicon-cog"></span> Admin</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">

    <div>
        <?php echo $display_block; ?>
    </div><!-- /content -->

</div>

<footer class="footer">
    <div class="container">
        <p class="text-muted">If you have any questions, contact Andrew Sleigh: P.A.Sleigh@leeds.ac.uk</p>
    </div>
</footer>

<!--Body content-->
<script type="text/javascript">

    function fetch_select(val)
    {
        $.ajax({
            type: 'post',
            url: 'fetch_data.php',
            data: {
                get_option:val,
                get_pm_id:val
            },
            success: function (response) {
                document.getElementById("student_select").innerHTML=response;
                document.getElementById("student_select_div").style.display = 'block';
            }
        });

    }
</script>
<script type="text/javascript">

    function fetch_sid_select(val) {

        pm_id = document.getElementById("pm_id").value;
        sid = document.getElementById("student_select").value;
        document.getElementById("container").style.display = 'block';
        //alert("pm_id=" + pm_id);
        //alert("sid=" + sid);
        //alert("val=" + val);
        $.ajax({
            type: 'post',
            url: 'fetch_student_chart_data.php',
            data: {
                get_pm_id: pm_id,
                get_sid: val
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
                my_boxplots.series[2].data = mydata.module_marks;
                //my_boxplots.series[1].data = [ [0, 40], [4, 75] ];

                my_boxplots.xAxis.categories = mydata.module_summary_names;
                my_boxplots.title.text = mydata.plot_title;

                mean_val = mydata.student_mean;

                my_boxplots.yAxis.plotLines[0].value = mean_val;
                my_boxplots.yAxis.plotLines[0].label.text = "Year average " + mean_val.toFixed(1);
                my_boxplots.yAxis.plotLines[0].label.rotation = -90;

                chart = new Highcharts.Chart(my_boxplots);

            }
        });
    }

</script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script src="./student_box_charts.js"></script>

<script>
//    // Chart data
//
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
//    my_boxplots.series[2].data = <?php //echo json_encode($module_marks) ?>//;
//    //my_boxplots.series[1].data = [ [0, 40], [4, 75] ];
//
//    my_boxplots.xAxis.categories = <?php //echo json_encode($module_summary_names) ?>//;
//    my_boxplots.title.text = <?php //echo json_encode($plot_title) ?>//;
//
//    mean_val = <?php //echo json_encode($student_mean) ?>//;
//
//    my_boxplots.yAxis.plotLines[0].value = mean_val;
//    my_boxplots.yAxis.plotLines[0].label.text = "Year average " + mean_val.toFixed(1) ;
//    my_boxplots.yAxis.plotLines[0].label.rotation = -90;
//    chart = new Highcharts.Chart(my_boxplots);
//
</script>

</BODY>
</HTML>


