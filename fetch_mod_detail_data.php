<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

(@include_once("./json_encode_for_php51.php")) OR die("Cannot find this file to include: json_encode_for_php51.php<BR>");

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

    }

    //echo($mod_summary_table_name);
    // Get a list of students and put in the select list
    if ($level_val > 5) {
        // All
        $query = "SELECT * FROM $mod_summary_table_name WHERE 1 ORDER BY number_in_class DESC ";
    } else {
        $query = "SELECT * FROM $mod_summary_table_name WHERE level='$level_val'  ORDER BY number_in_class DESC ";
    }

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
    $student_list_options = "";

    $num = $results->rowCount();

    $module_irq_data = array();
    $module_names = array();
    $module_means = array();
    $module_attend = array();
    //echo("<option selected disabled>Choose a student</option>\n";
    //echo("<option selected disabled>Choose a student</option>\n");
    if ($num > 0) {
        foreach ($recs as $rec) {
            $module_code = $rec['module_code'];
            $school_short_name = $rec['school_short_name'];
            $module_table = $rec['module_table'];
            $module_prefix = $rec['module_prefix'];
            $module_number_of_students = $rec['number_in_class'];
            $module_number_of_activities = $rec['number_of_activities'];
            $module_mean_attend = $rec['mean'];
            $module_level = $rec['level'];

            $mod_summary_data[] = array("$module_code", "$module_number_of_activities", "$module_number_of_students", "$module_mean_attend", "$module_level");

            $num_decimals = 1;
            $module_min = round(floatval($rec['min']),$num_decimals);
            $module_max = round(floatval($rec['max']),$num_decimals);
            $module_q25 = round(floatval($rec['q25']),$num_decimals);
            $module_q50 = round(floatval($rec['q50']),$num_decimals);
            $module_q75 = round(floatval($rec['q75']),$num_decimals);
            $module_mean = round(floatval($rec['mean']),$num_decimals);

            $module_irq_data[] =array($module_min,$module_q25,$module_q50,$module_q75,$module_max);
            $module_means[] = $module_mean;
            $module_names[] = $module_code;
            //array('module_iqrs'=>$module_iqrs, 'module_means'=>$module_means,
            //'module_summary_names'=>$module_summary_names, 'plot_title'=>$plot_title);

            // Get the detailed attendance data from the module table

            $query = "SELECT * FROM $module_table WHERE 1 ORDER BY date_time_start ASC";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $module_recs = $results->fetchAll();
            $number_of_decimals = 1;

            $module_attend_date_time = array();
            $module_attend_pc = array();
            $module_attend_number_in_class = array();
            foreach($module_recs as $module_rec){
                $number_in_class = floatval($module_rec['number']);
                $attended_class = floatval($module_rec['attend']);
                $date_time_start_str = $module_rec['date_time_start'];
                if( $attended_class > 0 && $number_in_class > 0) {
                    $attend_pc = round($attended_class / $number_in_class * 100.0, $number_of_decimals);
                }else{
                    $attend_pc = 0.0;
                }
                //$module_attend_date_time[] = array("y" => $date_time_start_str, "num" => $number_in_class);
                $module_attend_date_time[] = $date_time_start_str;
                //$module_attend_pc[] = $attend_pc;
                $module_attend_pc[] = array("y" => $attend_pc, "num" => $number_in_class);
                $module_attend_number_in_class[] = $number_in_class;
            }

            $module_attend[] = array("date_time" => $module_attend_date_time, "attendance" => $module_attend_pc, "number_in_class" => $module_attend_number_in_class);
        }
    } else {
        $mod_summary_data = Array();
    }


    $plot_title = "Module summary attendance data for $school_title session $year_session";
    $graph_data = Array('module_iqrs' => $module_irq_data, 'module_summary_data' => $mod_summary_data, 'module_means' => $module_means, 'module_names' => $module_names, 'plot_title' => $plot_title, 'module_attend_data' => $module_attend);

    echo json_encode($graph_data);


}
