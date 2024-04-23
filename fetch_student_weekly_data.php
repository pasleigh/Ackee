<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

(@include_once("./json_encode_for_php51.php")) OR die("Cannot find this file to include: json_encode_for_php51.php<BR>");
(@include_once("./student_data_functions.php")) OR die("Cannot find this file to include: student_data_functions.php<BR>");
ini_set('memory_limit', '256M'); // need plenty of memory
error_log("script was called, processing request...");
//$pm_id = $_POST['get_pm_id'];

if (isset($_POST['get_pm_id'])) {
    $pm_id = $_POST['get_pm_id'];
    $level_val = $_POST['get_level'];

    $year_session = "1066";
    $school_title = "no school set";

    // Get a list of all years of data in the database
    $query = "SELECT * FROM $definition WHERE id='$pm_id'";
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

        $get_data = $results->fetch();

        $school_short_name = $get_data['school_short_name'];

        $school_short_name_lc = strtolower($school_short_name);
        $school_title = $get_data['school_title'];
        $module_prefix = $get_data['default_module_prefix'];
        $running_title = $get_data['running_title'];
        $running_subtitle = $get_data['running_subtitle'];
        $year_session = $get_data['year'];

        $year_begin = substr($year_session, 0, 4);
        $year_end = intval($year_begin) + 1;
        $year_end_short = $year_end - 2000;
        $raw_data_issue = $get_data['raw_data_issue'];
        $raw_data_version = $get_data['raw_data_version'];

        $school_short_name_lc = strtolower($school_short_name);
        $mod_summary_table_name = "summary_$school_short_name_lc" . "_$year_session";

        $version = 1;
        $student_list_table = GetStudentListTableName($school_short_name, $year_session, $version);

        // create the student-module-list table
        $student_module_table = GetStudentModuleTableName($school_short_name, $year_session, $version);

        $raw_data_table = GetRawDataTableName($school_short_name, $year_session, $raw_data_issue, $raw_data_version);

    }


    // Get the student details
    $query = "SELECT * FROM $student_list_table WHERE level='$level_val' ORDER BY family_name";
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
    $num = $results->rowCount();

    $student_data = array();
    $all_sids = array();
    $all_family_names = array();
    if ($num > 0) {
        foreach ($recs as $rec) {
            $sid = $rec['sid'];
            $all_sids[] = $sid;
            $family_name = $rec['family_name'];
            $all_family_names[] = $family_name;
            $first_name = $rec['first_name'];
            $email = $rec['email'];
            $student_level = $rec['level'];
            $mean_attendance = $rec['mean_attendance'];

            $student_data[] = array("$sid", "$first_name", "$family_name", "$mean_attendance", "$student_level", "$email");
        }
    }
    //$graph_data = Array('student_summary_data' => $student_data);
   // echo json_encode($graph_data);
   // exit();
    //echo($mod_summary_table_name);
    // Get a list of modules for this student
    $x_axis_cats = array();
    $heatmap_data = array();
    $sid_for_chart = array();

    $max_num_students = 50;
    $k_max = ceil(sizeof($all_sids)/$max_num_students);
    for($k = 0; $k < $k_max; $k++) {
        $heatmap_data_block = array();
        for ($i = 0; $i < 12; $i++) {
            $x_axis_cats[$i] = "w$i";

            for ($j = $k*$max_num_students; $j < ($k+1)*$max_num_students; $j++) {
                if($j == sizeof($all_sids)){break;}
                $sid = $all_sids[$j];
                //$sid_for_chart[] = $sid . " " . $all_family_names[$j] . " $j";

                $week_count = 0;
                $week_sum = 0.0;
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

                $recs = $results->fetchAll();
                foreach ($recs as $rec) {
                    $activity_week_val = floatval($rec["w$i"]);
                    if ($activity_week_val > -0.5) {
                        $week_count++;
                        $week_sum += $activity_week_val;
                    }
                }
                if ($week_count > 0) {
                    $val = round($week_sum / $week_count, 1);
                } else {
                    $val = null;
                }
                $heatmap_data_block[] = array($i, $j, $val);
            }
        }
        $heatmap_data[] = $heatmap_data_block;
    }
    for ($j = 0; $j < sizeof($all_sids); $j++) {
        $sid = $all_sids[$j];
        $sid_for_chart[] = $sid . " " . $all_family_names[$j] . " : $j";
    }

    $plot_title = "Student weekly attendance data for $school_title session $year_session";
    $graph_data = Array('student_summary_data' => $student_data, 'attend_data' =>  $heatmap_data, "chart_sids" => $sid_for_chart, "weeks" => $x_axis_cats, "plot_title" => $plot_title);

    echo json_encode($graph_data);
}
