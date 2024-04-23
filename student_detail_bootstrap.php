<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");

(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");
(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");
(@include_once("./json_encode_for_php51.php")) OR die("Cannot find this file to include: json_encode_for_php51.php<BR>");

(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");

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
    $display_block .= "<h2>Choose a School / Session / Level that you want to see the modules for<BR>\n";
    $display_block .= "Then select the student to show their attendance histories</H2>\n";

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

    $display_block .= "<option value='0'>Undefined level</option>\n";
    $display_block .= "<option selected value='1'>Level 1</option>\n";
    $display_block .= "<option value='2'>Level 2</option>\n";
    $display_block .= "<option value='3'>Level 3</option>\n";
    $display_block .= "<option value='5'>Level 5</option>\n";
    //$display_block .= "<option value='6'>All levels</option>\n";
    $display_block .= "</select>\n";
    $display_block .= "</div>";
    $display_block .= "</div>";

    $display_block .= "</div>";

    // Get the complete module list

    $display_block .= "\n<input type='hidden' name='year' id='year' value=''>\n";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"select\">\n";
    //$display_block .= "<input type='hidden' name='pm_id' value='$pm_id_s'>\n";

    //$display_block .= "<p><input type=\"submit\" id=\"submitbutton\" name=\"submit\" value=\"View the selected data\"></p>\n";

    //$display_block .= "</form>\n";



    $display_block .= "<div id='container' style=' border-style: solid; border-width: 3px; margin-top: 10px; margin-bottom: 10px;  border-color: black; padding: 5px; display: none;'></div>\n";

    $display_block .= "<table id='module_list_table' class='hover compact cell-border' cellspacing='0' width='100%'>\n";
    $display_block .= "<thead>\n";
    $display_block .= "<tr>\n";
    $display_block .= "<th> </th>\n";
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

    $display_block .= "<div id='mod_table' style='height: 480px; width:100%; border-style: solid; border-width: 2px; margin-top: 10px; margin-bottom: 10px;  border-color: silver; padding: 5px; display: none;'></div>\n";


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing : Student Detail</title>
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
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script src="./student_module_combined_column_charts.js"></script>

<script type="text/javascript">
    var g_iqr_data;
    var g_iqr_names;
    var g_iqr_means;
    var g_iqr_title;

    var g_module_attend_data;
    var g_module_attend_date_times;
    var g_module_attend_nums;

    var g_student_summary_data;
    var g_student_modules;
    var g_level;
    var g_pm_id;
    var g_sid;
    var g_module_summary_data;
    var g_module_activities_data;
    var g_student_data;

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
        var year = 201617;
        $('#container').html('');// clear
        $("#mod_table").show();
        $("#mod_table").html("Level value " + level_val + "\n");
        $("#mod_table").append("School text " + school_text + "<BR>\n");
        $("#mod_table").append("School lookup id " + pm_id + "<BR>\n");

        g_level = level_val;
        g_pm_id = pm_id;
        waitCursor();
        $.ajax({
            type: 'post',
            url: 'fetch_test.php',
            url: 'fetch_student_detail_data.php',
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
                    //var id = mydata[i][0];
                    var id = "s" + i;
                    var name = "student";//i.toString();
                    var checked = "";
                    if(i < 0){
                        checked = "checked";
                    }

                    var my_row_data = [ "<input  value='1' name='" + name + "' " + "id='" + id + "'" + checked + " type='radio' />", mydata[i][0], mydata[i][1], mydata[i][2], mydata[i][3], mydata[i][4], mydata[i][5]];
                    //$('#module_list_table > tbody:last-child').append(my_row);
                    table.row.add(my_row_data).draw();
                }

                // Store the iqr data in a global array
                /*
                g_iqr_data = data.module_iqrs;
                g_iqr_means = data.module_means;
                g_iqr_names = data.module_names;
                g_iqr_title = data.plot_title;
                g_module_summary_data = data.module_summary_data;
                g_module_attend_data = data.module_attend_data;
*/

                g_student_summary_data = data.student_summary_data;
                g_student_modules = data.student_modules;

                //draw_checked_modules();
            },
            error: function (jqXHR, exception) {
                var msg = 'url: fetch_student_detail_data.php\n';
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

    /*
    $('#module_list_table :radio').change(function() {
        draw_checked_modules()
    });
*/
    function draw_checked_modules(){
        var $radio_id = $('input[name=student]:checked', '#module_list_table').attr('id');
        var i =parseInt($radio_id.substr(1));
        g_sid = g_student_summary_data[i][0];
        waitCursor();
        $.ajax({
            type: 'post',
            url: 'fetch_student_module_detail_data.php',
            data: {
                'get_level': g_level,
                'get_pm_id': g_pm_id,
                'get_sid': g_sid
            },
            dataType: "json",
            success: function (data) {
                var mydata = data.student_summary_data;

                g_module_summary_data = data.module_summary_data;
                g_module_attend_data = data.module_attend_data;
                g_student_data = data.student_data;

                var selected = [];
                var module_attend_data = [];
                var module_attend_numbers = [];
                var module_attend_date_times = [];
                var student_attend_data = [];

                // The chart is drawn in this div, so make it visible
                document.getElementById("container").style.display = 'block';
                var test_data = [
                    [5, 10, 40, 70, 91]
                ];

                // Clear the container
                $('#container').html('');
                var student_name = "<H3  class='text-center'>Attendance Records for " + g_student_data[1] + " " + g_student_data[2] + " (" + g_student_data[0] + ")</H3>";
                $('#container').html(student_name);
                //alert($('input[name=mod]:checked', '#module_list_table').attr('id').substr(1));
                //var $radio_id = $('input[name=student]:checked', '#module_list_table').attr('id');
                //var i =parseInt($radio_id.substr(1));
                //$('#container').append(g_sid + '<BR>');

                var module_list = "";
                for(i = 0; i< g_module_summary_data.length ; i++) {
                    // Only plot modules whose code is longer than 4 i.e. likley to be a proppoer module
                    if (g_module_summary_data[i][1].length > 4) {
                        var module_code = g_module_summary_data[i][1];
                        //module_list += " : " + module_code + '<BR';


                        //$('#container').append(module_list + '<BR>');
                        //iqr_data[j] = g_iqr_data[i];
                        //iqr_means[j] = g_iqr_means[i];
                        //iqr_names[j] = g_iqr_names[i];
                        module_attend_data = g_module_attend_data[i].attendance;
                        module_attend_date_times = g_module_attend_data[i].date_time;
                        module_attend_numbers = g_module_attend_data[i].number_in_class;
                        student_attend_data = g_module_attend_data[i].student_attendance;

                        var student_chart_data = Array();
                        for(var j = 0 ; j < student_attend_data.length; j++){
                            if(student_attend_data[j] == 1 ) {
                                student_chart_data[j] = {y: 95, marker: {fillColor: 'green'}};
                            }else if(student_attend_data[j] == 3 ){
                                    student_chart_data[j] = {y: 94.9, marker: { fillColor: 'Gold' }};//Gold, PaleGreen
                            }else if (student_attend_data[j] == 0){
                                student_chart_data[j] = {y: 94.85, marker: { fillColor: 'red' }};
                            }else{
                                attend_value = null;
                                student_chart_data[j] = null;
                            }
                            //student_chart_data[j] = '{y:' + attend_value + ', marker: { fillColor: ' + color_name + attend_value;
                        }

                        my_columnplot.series[0].data = module_attend_data; //test_data;
                        my_columnplot.series[1].data = student_chart_data; //test_data;

                        my_columnplot.xAxis.categories = module_attend_date_times;
                        //var student_name = g_student_data[1] + " " + g_student_data[2] + " (" + g_student_data[0] + ")";
                        my_columnplot.title.text = "Attendance record for module " + module_code;

                        //chart = new Highcharts.Chart(my_columnplot);
                        //$('#container').highcharts(my_columnplot);

                        var chartDiv = document.createElement('div'); // Create a new div
                        var ht = 250;
                        chartDiv.style.height =ht+"px";
                        $('#container').append(chartDiv); // Append it to your container
                        $(chartDiv).highcharts(my_columnplot); // Initialize highcharts in that div
                        //selected.push($(this).attr('name'));
                    }
                }

            },
            error: function (jqXHR, exception) {
                var msg = 'url: fetch_student_module_detail_data.php\n';
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
        $('input[type="radio"]', rows).prop('checked', this.checked);
    });
    // Handle click on checkbox to set state of "Select all" control
    $('#module_list_table tbody').on('change', 'input[type="radio"][name="student"]', function(){
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
    $(document).ready(function(){
        var pm_id = $("#pm_id option:selected").val();
        if($.isNumeric(pm_id)){
            fetch_level_select(0);
        }
    })
</script>
</BODY>
</HTML>


