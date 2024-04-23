<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

(@include_once("./json_encode_for_php51.php")) OR die("Cannot find this file to include: json_encode_for_php51.php<BR>");
(@include_once("./student_data_functions.php")) OR die("Cannot find this file to include: student_data_functions.php<BR>");

error_log("script was called, processing request...");
//$pm_id = $_POST['get_pm_id'];

if (isset($_POST['get_pm_id'])) {
    $pm_id = $_POST['get_pm_id'];
    $level_val = $_POST['get_level'];
    $sid = $_POST['get_sid'];

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
    $query = "SELECT * FROM $student_list_table WHERE sid='$sid'";
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
    if ($num > 0) {
        foreach ($recs as $rec) {
            $sid = $rec['sid'];
            $family_name = $rec['family_name'];
            $first_name = $rec['first_name'];
            $email = $rec['email'];
            $student_level = $rec['level'];
            $mean_attendance = $rec['mean_attendance'];

            $student_data = array("$sid", "$first_name", "$family_name", "$mean_attendance", "$student_level", "$email");
        }
    }

    //echo($mod_summary_table_name);
    // Get a list of modules for this student
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
    $student_list_options = "";

    $num = $results->rowCount();
    $num = $results->rowCount();

    $module_irq_data = array();
    $module_names = array();
    $module_means = array();
    $module_attend = array();
    $module_summary_data = array();

    $module_attend_date_time = array();
    $module_attend_pc = array();
    $module_attend_number_in_class = array();
    $attendance_record = array();
    //echo("<option selected disabled>Choose a student</option>\n";
    //echo("<option selected disabled>Choose a student</option>\n");
    if ($num > 0) {
        foreach ($recs as $rec) {
            $module_code = $rec['module_code'];
            if(strlen($module_code)>4) {
                $module_table = $rec['module_table'];
                $mean_module_attendance = $rec['mean_module_attendance'];

                $module_summary_data[] = array("$sid", "$module_code", "$module_table", "$mean_module_attendance");

                $module_raw_table = GetModuleRawAttendTableName($school_short_name, $year_session, $module_code, 1);

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
                $attendance_record = array();
                foreach ($module_recs as $module_rec) {
                    $number_in_class = floatval($module_rec['number']);
                    $attended_class = floatval($module_rec['attend']);
                    $date_time_start_str = $module_rec['date_time_start'];
                    if ($attended_class > 0 && $number_in_class > 0) {
                        $attend_pc = round($attended_class / $number_in_class * 100.0, $number_of_decimals);
                    } else {
                        $attend_pc = 0.0;
                    }
                    //$module_attend_date_time[] = array("y" => $date_time_start_str, "num" => $number_in_class);
                    $module_attend_date_time[] = $date_time_start_str;
                    //$module_attend_pc[] = $attend_pc;
                    $module_attend_pc[] = array("y" => $attend_pc, "num" => $number_in_class);
                    $module_attend_number_in_class[] = $number_in_class;

                    //  Search the raw data for this activity, date and sid
                    //  search on the original date and start time formats
                    // if not found then the student was not required to attend

                    $date_original = $module_rec['date_original'];
                    $start_time_original = $module_rec['start_time_original'];
                    $activity = $module_rec['activity'];
                    $query = "SELECT * FROM $raw_data_table WHERE sid='$sid' AND date='$date_original' AND start_time='$start_time_original' AND activity='$activity'";
                    $use_module_raw = false;
                    $query = "SELECT * FROM $module_raw_table WHERE sid='$sid' AND date_original='$date_original' AND start_time_original='$start_time_original' AND activity='$activity'";
                    $use_module_raw = true;
                    try {
                        $results = $db->query($query);
                    } catch (PDOException $ex) {
                        $this_function = __FUNCTION__;
                        echo "An Error occured accessing the database in function: $this_function <BR>\n";
                        echo(" Query = $query<BR> \n");
                        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                        exit();
                    }
                    $attend_recs = $results->fetchAll();
                    //var_dump($attend_recs);
                    //exit();
                    $num = $results->rowCount();
                    if ($num == 1) {
                        foreach ($attend_recs as $attendance_rec) {
                            // get the attendance record
                            // assign 0 or 1
                            $attend_code1 = $attendance_rec['attend_code1'];
                            if ($raw_data_version == 2 and $use_module_raw == false) {// civil
                                $attend_code2 = $attendance_rec['attend_code2'];
                                if ($attend_code2 != "") {
                                    $attend_code1 = $attend_code2;
                                }
                            }
                            //$num_present = $num_AA + $num_PB + $num_PR + $num_PS;
                            //$num_absent = $num_AD + $num_AR + $num_UA + $num_NR;
                            $attend = 0;
                            if ($attend_code1 == "AA" or $attend_code1 == "PB" or $attend_code1 == "PR" or $attend_code1 == "PS") {
                                $attend = 1;
                            }
                            if ($attend_code1 == "PS") {
                                $attend = 3;
                            }
                            $attendance_record[] = $attend;
                        }
                    } else {
                        $attendance_record[] = -1;
                    }

                }
                /*
                $query = "SELECT * FROM $module_raw_table WHERE sid='$sid' AND date_original='$date_original' ";
                try {
                    $results = $db->query($query);
                } catch (PDOException $ex) {
                    $this_function = __FUNCTION__;
                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                    echo(" Query = $query<BR> \n");
                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                    exit();
                }
                $attend_recs = $results->fetchAll();
                $attendance_record = array();
                $num = $results->rowCount();
                foreach ($attend_recs as $attendance_rec) {
                    // get the attendance record
                    // assign 0 or 1
                    $attend_code1 = $attendance_rec['attend_code1'];
                    //$num_present = $num_AA + $num_PB + $num_PR + $num_PS;
                    //$num_absent = $num_AD + $num_AR + $num_UA + $num_NR;
                    $attend = 0;
                    if ($attend_code1 == "AA" or $attend_code1 == "PB" or $attend_code1 == "PR" or $attend_code1 == "PS") {
                        $attend = 1;
                    }
                    $attendance_record[] = $attend;
                }
                */
                $module_attend[] = array("date_time" => $module_attend_date_time, "attendance" => $module_attend_pc, "number_in_class" => $module_attend_number_in_class, "student_attendance" => $attendance_record);
            }

        }
    } else {
        $module_summary_data = Array();
    }


    $plot_title = "Student detailed attendance data for $school_title session $year_session";
    //$graph_data = Array('module_iqrs' => $module_irq_data, 'module_summary_data' => $mod_summary_data, 'module_means' => $module_means, 'module_names' => $module_names, 'plot_title' => $plot_title, 'module_attend_data' => $module_attend);
    $graph_data = Array('module_summary_data' => $module_summary_data, 'plot_title' => $plot_title, 'module_attend_data' => $module_attend, 'student_data' => $student_data);

    echo json_encode($graph_data);


}
