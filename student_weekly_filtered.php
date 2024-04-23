<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");

(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");
(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");
(@include_once("./json_encode_for_php51.php")) OR die("Cannot find this file to include: json_encode_for_php51.php<BR>");

(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");

ini_set('memory_limit', '256M'); // need plenty of memory
$display_block = "";


$welcome_phrase = "Hello $this_fullname ($this_username)";
$display_block .= "<h4 style='color: darkgoldenrod'>$welcome_phrase</h4>\n";

//$display_block .= "<h2 style='font-size: x-small; color: darkgoldenrod'>$access_phrase</h2>\n";

if (true) {
    if (array_key_exists('pm_id', $_REQUEST) == false) {
        $_REQUEST['pm_id'] = null;
    }
    $pm_id = $_REQUEST['pm_id'];
    if (array_key_exists('pm_id', $_COOKIE) == false) {
        $_COOKIE['pm_id'] = null;
    }
    $pm_id = $_COOKIE['pm_id'];
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
        $school_short_name_lc = strtolower($school_short_name);
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

// Display the dropdowns to select the module and year
    $display_block .= "<h2><em>Filtered Large Class</em> data. Heat map view</h2>";
    $display_block .= "<div id='filter' style='visibility: hidden;'><h3>Showing data for sessions with clas size >" . $low_numbers_value[intval($id)] . " and attendance > " . $low_attendance_value[intval($id)] ."% </h3></div>";
    $display_block .= "<h2>Choose a School / Session that you want to see the Weekly view for</H2>\n";
    $display_block .= "<h3 style='color: firebrick;'>Please be patient - this will take about 10 seconds to load.</H3>\n";

    //$display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">\n";

    $display_block .= "<div class='row'>\n";
    $display_block .= "<div class='col-sm-6'>\n";
    $display_block .= "<label for='pm_id'>Select from the School / Session list:</label>";
    $display_block .= "<select class='form-control' name='pm_id'  id='pm_id' onchange='fetch_level_select(this.value);'>\n";

    $display_block .= "<option selected disabled>Choose a School / year</option>\n";
    $display_block .= $year_list_options;
    $display_block .= "</select>\n";
    $display_block .= "</div>";

    $display_block .= "<div class='col-sm-6'>\n";
    $display_block .= "<div id='level_select_div' style='display: none;'>\n";
    $display_block .= "<label for='pm_id'>Select the level</label>";
    $display_block .= "<select class='form-control' name='level_id'  id='level_id' onchange='update_level_list(this.value);'>\n";

    //$display_block .= "<option value='0'>Undefined level</option>\n";
    $display_block .= "<option selected value='1'>Level 1</option>\n";
    $display_block .= "<option value='2'>Level 2</option>\n";
    $display_block .= "<option value='3'>Level 3</option>\n";
    $display_block .= "<option value='5'>Level 5</option>\n";
    //$display_block .= "<option selected value='6'>All levels</option>\n";
    $display_block .= "</select>\n";
    $display_block .= "</div>";
    $display_block .= "</div>";

    $display_block .= "</div>";

    // Get the complete module list

    $display_block .= "\n<input type='hidden' name='year' id='year' value=''>\n";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"select\">\n";



    $display_block .= "<div id='container' style='border-style: solid; border-width: 3px; margin-top: 10px; margin-bottom: 10px;  border-color: black; padding: 5px; display: none;'></div>\n";

    $display_block .= "<table id='module_list_table' class='hover compact cell-border' cellspacing='0' width='100%'>\n";
    $display_block .= "<thead>\n";
    $display_block .= "<tr>\n";
    $display_block .= "<th><input name='select_all' value='1' id='module_list_table-select-all' type='checkbox' /></th>\n";
    $display_block .= "<th>Student ID</th>\n";
    $display_block .= "<th>First name</th>\n";
    $display_block .= "<th>Family Name</th>\n";
    $display_block .= "<th>Mean Attendance %</th>\n";
    $display_block .= "<th>Level</th>\n";
    $display_block .= "<th>Extra</th>\n";
    $display_block .= "</tr>\n";
    $display_block .= "</thead>\n";
    $display_block .= "<tbody>\n";

    $display_block .= "</tbody>\n";
    $display_block .= "</table>\n";

    $display_block .= "<div id='mod_table' style='height: 580px; width:100%; border-style: solid; border-width: 2px; margin-top: 10px; margin-bottom: 10px;  border-color: silver; padding: 5px; display: none;'></div>\n";


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing : Year Overview</title>
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

<!--Body content-->

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/heatmap.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script src="./weekly_heatmap_chart.js"></script>
<!-- <script src="./weekly_heatmap_extended_chart.js"></script> -->

<script type="text/javascript">
var g_heatmap_data;
var g_weeks;
var g_sids;
var g_title;


    function fetch_level_select(val)
    {
        $("#level_select_div").show();
        //alert("show level select value = " + $("#level_select_div option:selected").attr('value'));
        // refresh the module list
        update_level_list(1);
    }
    function update_level_list(val)
    {
        var level_val = val;
        //alert("show level select value from header= " + val);
        var school_text = $("#pm_id option:selected").text();
        var pm_id = $("#pm_id option:selected").val();
        // Set the pm_id as a cookie. Set for 7 days
        var days = 7; var seconds = days*24*60*60;
        docCookies.setItem("pm_id", pm_id, seconds)
        var low_attendance_value = <?php echo json_encode($low_attendance_value); ?>;
        var low_numbers_value = <?php echo json_encode($low_numbers_value); ?>;
        $("#filter").html("<h3>Showing data for sessions with class size > " + low_numbers_value[pm_id] + " and attendance > " + low_attendance_value[pm_id] + "%");
        $("#filter").css('visibility', 'visible');
        var year = 201617;
        $('#container').html('');// clear
        $("#mod_table").show();
        $("#mod_table").html("Level value " + level_val + "\n");
        $("#mod_table").append("School text " + school_text + "<BR>\n");
        $("#mod_table").append("School lookup id " + pm_id + "<BR>\n");
        waitCursor();
        $.ajax({
            type: 'post',
            url: 'fetch_student_weekly_filtered_data.php',
            data: {
                'get_level': level_val,
                'get_pm_id': pm_id
            },
            dataType: "json",
            success: function (data) {
                var table = $('#module_list_table').DataTable();
                table.clear().draw();

                var mydata = data.student_summary_data;
                for( var i = 0 ; i < mydata.length ; i++ ){
                   /*
                    var my_row = "<tr>";

                    my_row += "<td>" + "<input name='select_this' value='1' id='module_list_table-select-this' type='checkbox' />" + "</td>";
                    my_row += "<td>" + mydata[i] + "</td>";
                    my_row += "<td>" + "level" + "</td>";
                    my_row += "<td>" + "level" + "</td>";
                    my_row += "<td>" + "num" + "</td>";
                    my_row += "<td>" + "attendacne" + "</td>";
                    my_row += "</tr>\n";
                    */
                    var id = "s" + i;
                    var name = i.toString();
                    var checked = "";
                    if(i < 5000){
                        checked = "checked";
                    }
                    var my_row_data = [ "<input  value='1' name='" + name + "' " + "id='" + id + "'" + checked + " type='checkbox' /> : " + i, mydata[i][0], mydata[i][1], mydata[i][2], mydata[i][3], mydata[i][4], mydata[i][5]];
                    //$('#module_list_table > tbody:last-child').append(my_row);
                    table.row.add(my_row_data).draw();
                }

                // Stor the iqr data in a global array
                g_heatmap_data = data.attend_data;
                g_weeks = data.weeks;
                g_sids = data.chart_sids;
                g_title = data.plot_title;

                draw_checked_modules();

            },
            error: function (jqXHR, exception) {
                var msg = 'url: fetch_student_weekly_data.php\n';
                if (jqXHR.status === 0) {
                    msg += 'Not connect.\n Verify Network.';
                } else if (jqXHR.status == 404) {
                    msg += 'Requested page not found. [404]';
                } else if (jqXHR.status == 500) {
                    msg += 'Internal Server Error [500].';
                } else if (exception === 'parsererror') {
                    msg += 'Requested JSON parse failed.';
                } else if (exception === 'timeout') {
                    msg += 'Time out error.';
                } else if (exception === 'abort') {
                    msg += 'Ajax request aborted.';
                } else {
                    msg += 'Uncaught Error.\n' + jqXHR.responseText;
                }
                msg += "\nResponse message: " + jqXHR.responseText;
                $('#mod_table').append(msg);
                alert(msg);
            }
        });

    }

    $('#module_list_table :checkbox').change(function() {
        draw_checked_modules()
    });

    function draw_checked_modules(){
        var selected = [];
        var iqr_data = [];
        var iqr_means = [];
        var iqr_names = [];

        $('#container').html('');
        $('#container').html(g_title);

        var j = 0;
        /*
        $('#module_list_table input:checked').each(function() {
            var i = parseInt($(this).attr('name'));
            iqr_data[j] = g_iqr_data[i];
            iqr_means[j] = g_iqr_means[i];
            iqr_names[j] = g_iqr_names[i];
            //selected.push($(this).attr('name'));
            j++;
        });
        */
        // The chart is drawin in this div, so make it visible
        document.getElementById("container").style.display = 'block';
        var test_data = [
            [0,0,10],[0,1,19],[0,2,8],[0,3,24],[0,4,67],
            [1,0,92],[1,1,58],[1,2,78],[1,3,117],[1,4,48],
            [2,0,35],[2,1,15],[2,2,123],[2,3,64],[2,4,52],
            [3,0,72],[3,1,132],[3,2,114],[3,3,19],[3,4,16],
            [4,0,38],[4,1,5],[4,2,8],[4,3,117],[4,4,115],
            [5,0,88],[5,1,32],[5,2,12],[5,3,6],[5,4,120],
            [6,0,13],[6,1,44],[6,2,88],[6,3,98],[6,4,96],
            [7,0,31],[7,1,1],[7,2,82],[7,3,32],[7,4,30],
            [8,0,85],[8,1,97],[8,2,123],[8,3,64],[8,4,84],
            [9,0,47],[9,1,114],[9,2,31],[9,3,48],[9,4,91]
        ];
        //console.log(g_heatmap_data);
        //console.log(g_weeks);
        //console.log(g_sids);

        my_heatmap.yAxis.categories = g_sids;
        for(j = 0 ; j < g_heatmap_data.length; j++) {
            my_heatmap.series[0].data = g_heatmap_data[j]; //test_data;
            //console.log(JSON.stringify(my_heatmap.series[0].data));

            my_heatmap.xAxis.categories = g_weeks;//['Alexander', 'Marie', 'Maximilian', 'Sophia', 'Lukas', 'Maria', 'Leon', 'Anna', 'Tim', 'Laura'];

            my_heatmap.yAxis.reversed = true;
            my_heatmap.title.text = g_title;//"Student module attendance by week";

            var num_students;
            if(j < g_heatmap_data.length-1){
                num_students = 50;
            }else{
                num_students = g_sids.length % 50;
            }
            var ht = 15*num_students;

            var chartContainerID = "container" + j;
            var chartDiv = document.createElement('div'); // Create a new div
            chartDiv.id = chartContainerID;
            chartDiv.style.height =ht+"px";

            $('#container').append(chartDiv); // Append it to your container

            $(chartDiv).highcharts(my_heatmap); // Initialize highcharts in that div
        }
    }

    $(function () {
        $("#module_list_table").dataTable(
            {
                //"bSort":    false,
                "paging": false,
                //"ordering": false,
                //"info":     false,
                "searching": false
            }
        );

    });
    // Handle click on "Select all" control
    $('#module_list_table-select-all').on('click', function(){
        // Check/uncheck all checkboxes in the table
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });
    // Handle click on checkbox to set state of "Select all" control
    $('#module_list_table tbody').on('change', 'input[type="checkbox"]', function(){

        // redraw
        draw_checked_modules();
        /*
        // If checkbox is not checked
        if(!this.checked){
            var el = $('#module_list_table-select-all').get(0);
            // If "Select all" control is checked and has 'indeterminate' property
            if(el && el.checked && ('indeterminate' in el)){
                // Set visual state of "Select all" control
                // as 'indeterminate'
                el.indeterminate = true;
            }
        }
        */
    });

    $(document).ajaxComplete(function(event, request, settings) {
        $('*').css('cursor', 'default');
    });

    function waitCursor() {
        $('*').css('cursor', 'progress');
    }

    $('#activelist :checkbox')
</script>


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
<script>
    $(document).ready(function(){
        var pm_id = $("#pm_id option:selected").val();
        if($.isNumeric(pm_id)){
            fetch_level_select(0);
        }
    })
</script>
</BODY>
</HTML>


