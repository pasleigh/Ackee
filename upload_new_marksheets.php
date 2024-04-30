<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");

(@include_once("./utility_functions.php")) OR die("Cannot read utility_functions file<BR>");
(@include_once("./staff_student_functions.php")) OR die("Cannot staff_student_functions file<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");

//(@include_once('./PHPExcel-1.8/Classes/PHPExcel.php')) OR die("Cannot read PHPExcel.php file<BR>");
//(@include_once('./PHPExcel-1.8/Classes/PHPExcel/IOFactory.php')) OR die("Cannot read IOFactory.php file<BR>");

(@include_once("./iqr.php")) OR die("Cannot find this file to include: iqr.php<BR>");

$display_block = "";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

error_reporting(E_ALL ^ E_NOTICE);
require_once  './vendor/autoload.php';

// Check the admin level - done in setup-params

$display_block .= "<h2 style='font-size: x-large; color: darkgoldenrod'>$access_phrase</h2>\n";

if ($access) {

    if (array_key_exists('op', $_POST) == false) {
        $_POST['op'] = null;
    }
    if (array_key_exists('op', $_POST)) {
        $POST_OP = $_POST['op'];
    } else {
        $POST_OP = "";
    }

    if (array_key_exists('show_table_chk', $_POST) == false) {
        $_POST['show_table_chk'] = null;
    }
    $show_table = $_POST['show_table_chk'];

    $display_block .= "<h1>Upload marksheets</h1>";


    if ($POST_OP == "new") {
        $display_block .= "<P>A New member of staff.</P>";

        $display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]?pm_id=$pm_id\">";

        $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"submit_new\">";

        $display_block .= "<p><input type=\"submit\" id=\"submitbutton_new\" name=\"submit\" value=\"Submit details of this new staff member.\"></p>";

        $display_block .= "</form>";


        $display_block .= "<hr>\n";
    } elseif ($POST_OP == "submit_edited") {
    } elseif ($POST_OP == "submit_new") {
    } elseif ($POST_OP == "upload_xls") {
        echo("<PRE>");
        print_r($_REQUEST);
        print_r($_FILES['attend_xls']);
        echo("</PRE>");
        $attend_xls_file_name = $_FILES['attend_xls']['name'];
        $attend_xls_file_size = $_FILES['attend_xls']['size'];
        $attend_xls_file_tmp = $_FILES['attend_xls']['tmp_name'];
        $attend_xls_file_type = $_FILES['attend_xls']['type'];
        //echo("File name: $attend_xls_file_name<BR>");
        //echo("File size: $attend_xls_file_size<BR>");
        //echo("File tmp: $attend_xls_file_tmp<BR>");
        //echo("File type: $attend_xls_file_type<BR>");


        //$m_year_end = substr($module_xls_file_name, 0, 4);
        //$m_level = substr($module_xls_file_name, 5, 1);
        //$m_version = substr($module_xls_file_name, 7, 1);

        //$s_year_end = substr($summary_xls_file_name, 0, 4);
        //$s_level = substr($summary_xls_file_name, 5, 1);
        //$s_version = substr($summary_xls_file_name, 7, 1);

        //if (($m_year_end != $s_year_end) or ($m_level != $s_level) or ($m_version != $s_version)) {
        //    $display_block .= "<h2>The files must be for the same year, level and version</h2>";
        //} else {
        /*

            $year_end = $m_year_end;
            $level = $m_level;
            $version = $m_version;
            $year_begin = $year_end - 1;
            $year_end_short = $year_end - 2000;

            $num_modules = 20;
*/
            // location markers for teh summary sheet


            $sid_col = 0;
            $name_col = 1;
            $tutor_initial_col = 2;

            if (!$_FILES['attend_xls']['error']) {
                if ($attend_xls_file_name != "") {
                    $PHP_EXCEL_filetype = IOFactory::identify($attend_xls_file_tmp);
                    $module_xls_PHPExcel_file_type = "PHPExcel file type: $PHP_EXCEL_filetype<BR>";
                    $objPHPExcelModules = IOFactory::load($attend_xls_file_tmp);
                    //$objPHPExcelModules = PHPExcel_IOFactory::load($attend_xls_file_tmp);
                    //$num_modules = $objPHPExcelModules->getSheetCount();
                    create_module_names_table_structure($db, $year_end, $level, $version, $module_names_table);

                    $module_means = array();
                    $module_iqrs = array();
                    $module_index = 0;
                    // get the module names
                    foreach ($objPHPExcelModules->getWorksheetIterator() as $worksheet) {
                        // Check for "Exam Title" in cell C1
                        // check for work in cel A1
                        //$KEY_WORD = $worksheet->getCellByColumnAndRow(1, 1)->getValue();
                        $col = 1;
                        $row = 1;
                        $KEY_WORD = $worksheet->getCell([$col, $row])->getValue();
                        if ($KEY_WORD == "PIDM_KEY") {
                            // Mech sheet i.e. the TBL_ files
                        }elseif($KEY_WORD == "ACTV_NAME") {
                            // Civil Sheet
                            $activity_col = 1;
                            $date_col = 2;
                            $start_time_col = 3;
                            $end_time_col = 4;
                            //
                            $attend_code1_col = 6;
                            $attend_code2_col = 7;

                            $sid_col = 8;
                            $student_name_col = 9;
                            $student_family_name_col = 10;
                            $highestRow = 10;//$worksheet->getHighestRow();
                            for($row = 2; $row <= $highestRow ; $row++){
                                $activity = $worksheet->getCell([$activity_col, $row])->getValue();
                                $date = $worksheet->getCell([$date_col, $row])->getValue();
                                $start_time = $worksheet->getCell([$start_time_col, $row])->getValue();
                                $end_time = $worksheet->getCell([$end_time_col, $row])->getValue();
                                $attend_code1 = $worksheet->getCell([$attend_code1_col, $row])->getValue();
                                $attend_code2 = $worksheet->getCell([$attend_code2_col, $row])->getValue();
                                $sid = $worksheet->getCell([$sid_col, $row])->getValue();
                                $student_name = $worksheet->getCell([$student_name_col, $row])->getValue();
                                $student_family_name = $worksheet->getCell([$student_family_name_col, $row])->getValue();

                                echo("$row<BR>");
                                echo("Activity      : $activity<BR>");
                                echo("Date          : $date<BR>");
                                echo("Start time    : $start_time<BR>");
                                echo("End time      : $end_time<BR>");
                                echo("Attend code 1 : $attend_code1<BR>");
                                echo("Attend code 2 : $attend_code2<BR>");
                                echo("SID           : $sid<BR>");
                                echo("Name          : $student_name<BR>");
                                echo("Family name   : $student_family_name<BR><BR>");
                            }
                            exit(0);
                            //$module_code = $worksheet->getTitle();
                            //$module_id = "";
                            //echo("Module: $module_code<BR>\n");
                            $col = 2;
                            $row = 2;
                            $name_cell = $worksheet->getCell([$col, $row]);
                            $module_name = $name_cell->getValue();
                            $col = 9;
                            $row = 1;
                            $credits_value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            // Add these to the table
                            $query = "INSERT $module_names_table set module_code='$module_code', full_name='$module_name', credits='$credits_value'";
                            try {
                                $results = $db->query($query);
                                $module_id = $db->lastInsertId();
                            } catch (PDOException $ex) {
                                $this_function = __FUNCTION__;
                                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                echo(" Query = $query<BR> \n");
                                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                //exit();
                            }

                            // create the module file
                            create_module_table_structure($db, $year_end, $module_code, $version, $module_table);

                            echo("Module: $module_code.<BR>\n");
                            echo("Module table: $module_table.<BR>\n");

                            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                            // find the last mark row
                            $highestRow = $worksheet->getHighestRow();
                            $m_sid_col = 1;
                            $m_first_mark_row = 8;
                            $m_labels_row = 5;

                            // find the registry mark column
                            $row = $m_labels_row;
                            for ($col = 1; $col < $highestColumnIndex; $col++) {
                                $value = $worksheet->getCell([$col, $row])->getValue();
                                if ($value == "Registry Mark") {
                                    $m_reg_mark_col = $col;
                                    break;
                                }
                            }
                            echo("Registry mark in col: $m_reg_mark_col<BR>\n");

                            // find the last civil student data row
                            for ($row = 1; $row <= $highestRow; ++$row) {
                                $value1 = $worksheet->getCellw([$m_sid_col + 1, $row])->getValue();
                                if ($value1 == 'Statistics') {
                                    $m_last_mark_row = $row - 1;
                                    break;
                                }
                            }
                            echo("Last module mark row: $m_last_mark_row<BR>\n");

                            // write the data to the table

                            $values = array();
                            for ($row = $m_first_mark_row; $row < $m_last_mark_row; $row++) {
                                $sid = $worksheet->getCellByColumnAndRow($m_sid_col, $row)->getValue();
                                if (is_numeric($sid)) {
                                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                                    if ($cell->isFormula()) {
                                        $val = $cell->getOldCalculatedValue();
                                    } else {
                                        $val = $cell->getValue();
                                    }
                                    if (is_numeric($val)) {
                                        // Add these to the table
                                        $query = "INSERT $module_table set sid='$sid', mark='$val' ";
                                        try {
                                            $results = $db->query($query);
                                        } catch (PDOException $ex) {
                                            $this_function = __FUNCTION__;
                                            echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                            echo(" Query = $query<BR> \n");
                                            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                            //exit();
                                        }
                                        $values[] = $val;
                                    }
                                }
                            }
                            // Get the iqr data
                            $iqr = BoxPlot5Stats($values);
                            $min = $iqr[0];
                            $q25 = $iqr[1];
                            $q50 = $iqr[2];
                            $q75 = $iqr[3];
                            $max = $iqr[4];
                            $mean = Average($values);
                            $module_iqrs[$module_index] = $iqr;
                            $module_means[$module_index] = [$module_index, $mean];

                            if ($module_id == "") {
                                // need to get it from the table
                                $query = "SELECT * FROM $module_names_table where module_code='$module_code'";
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
                                $recs = $results->fetch();
                                $module_id = $recs['id'];
                            }

                            // enter the iqr data into the table
                            $query = "UPDATE $module_names_table SET min='$min', max='$max', mean='$mean', q25='$q25', q50='$q50', q75='$q75' WHERE id='$module_id'";
                            try {
                                $results = $db->query($query);
                            } catch (PDOException $ex) {
                                $this_function = __FUNCTION__;
                                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                echo(" Query = $query<BR> \n");
                                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                exit();
                            }

                            $module_index++;
                        }
                    }

                    $objPHPExcelSummary = PHPExcel_IOFactory::load($summary_xls_file_tmp);

                    create_summary_marks_table_structure($db, $year_end, $level, $version, $num_modules, $summary_marks_table);

                    //$short_year_end = $m_year_end - 2000;
                    $plot_title = "Module box plots for year $year_begin-$year_end_short. Level $level "; //($version)";
                    if($version == 2){
                        $plot_title .= " (resit)";
                    }

                    foreach ($objPHPExcelSummary->getWorksheetIterator() as $worksheet) {
                        $worksheetTitle = $worksheet->getTitle();
                        $highestRow = $worksheet->getHighestRow(); // e.g. 10
                        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                        echo("Worksheet: $worksheetTitle\n");
                        $nrColumns = ord($highestColumn);
                        echo "<br>The worksheet " . $worksheetTitle . " has ";
                        echo $nrColumns . ' columns (A-' . $highestColumn . ') ';
                        echo ' and ' . $highestRow . ' rows.';

                        // find the first mark column
                        $row = $credits_row;
                        for ($col = $tutor_initial_col; $col < $highestColumnIndex; $col++) {
                            $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            if ($value != "") {
                                $first_mark_col = $col;
                                break;
                            }
                        }
                        echo("First mark in col: $first_mark_col<BR>\n");

                        // find the last mark col
                        $row = $names_row;
                        for ($col = $tutor_initial_col; $col < $highestColumnIndex; $col++) {
                            $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            if ($value == "Notes") {
                                $last_mark_col = $col - 1;
                                $notes_col = $col;
                                break;
                            }
                        }
                        echo("Last mark in col: $last_mark_col<BR>\n");

                        // find the last civil student data row
                        for ($row = 1; $row <= $highestRow; ++$row) {
                            $value1 = $worksheet->getCellByColumnAndRow($sid_col, $row)->getValue();
                            $value2 = $worksheet->getCellByColumnAndRow($name_col, $row)->getValue();
                            //echo("value 1: $value1    value 2: $value2<BR>");
                            if (strlen($value1) > 3) {
                                if (substr($value1, 0, 3) == 'ND-') {
                                    $last_data_row = $row - 1;
                                    break;
                                }
                            }
                            if ($value2 == 'Summary') {
                                $last_data_row = $row - 1;
                                break;
                            }
                        }
                        echo("Last data row: $last_data_row<BR>\n");

                        // get the summary names and put in the module names table
                        $module_summary_names = array();
                        for ($col = $first_mark_col; $col <= $last_mark_col; $col++) {
                            $module_index = $col - $first_mark_col + 1;
                            // do not add modules from outside the school
                            // i.e. not in the modules file
                            if ($module_index <= $num_modules) {
                                $summary_name = $worksheet->getCellByColumnAndRow($col, $names_row)->getValue();
                                $module_summary_names[] = $summary_name;
                                // enter the name
                                $query = "UPDATE $module_names_table SET summary_name='$summary_name' WHERE id='$module_index'";
                                try {
                                    $results = $db->query($query);
                                } catch (PDOException $ex) {
                                    $this_function = __FUNCTION__;
                                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                    echo(" Query = $query<BR> \n");
                                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                    //exit();
                                }
                            }
                        }

                        if ($show_table == 1) {
                            echo '<br>Data: <table border="1"><tr>';
                            for ($row = 1; $row <= $highestRow; ++$row) {
                                echo '<tr>';
                                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                                    if ($cell->isFormula()) {
                                        $val = $cell->getOldCalculatedValue();
                                    } else {
                                        $val = $cell->getValue();
                                    }

                                    $dataType = $cell->getDataType();//PHPExcel_Cell_DataType::dataTypeForValue($val);
                                    echo '<td>' . $val;
                                }
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                        for ($row = $credits_row + 1; $row < $last_data_row; $row++) {
                            $sid = $worksheet->getCellByColumnAndRow($sid_col, $row)->getValue();
                            if (is_numeric($sid)) {
                                $student_name = $worksheet->getCellByColumnAndRow($name_col, $row)->getValue();
                                $student_name = addslashes($student_name);
                                $tutor_initials = $worksheet->getCellByColumnAndRow($tutor_initial_col, $row)->getValue();
                                $notes = $worksheet->getCellByColumnAndRow($notes_col, $row)->getValue();
                                $notes = addslashes($notes);

                                // Add these to the table
                                $query = "INSERT $summary_marks_table set sid='$sid', name='$student_name', tutor='$tutor_initials',
                                                notes='$notes' ";
                                try {
                                    $results = $db->query($query);
                                } catch (PDOException $ex) {
                                    $this_function = __FUNCTION__;
                                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                    echo(" Query = $query<BR> \n");
                                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                    //exit();
                                }

                                // Now do the marks

                                for ($col = $first_mark_col; $col <= $last_mark_col; $col++) {
                                    $module_index = $col - $first_mark_col + 1;
                                    // do not add modules from outside the school
                                    // i.e. not in the modules file
                                    if ($module_index <= $num_modules) {
                                        $mark = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                                        if (is_numeric($mark)) {
                                            // enter the mark
                                            $query = "UPDATE $summary_marks_table SET m$module_index='$mark' WHERE sid='$sid'";
                                            try {
                                                $results = $db->query($query);
                                            } catch (PDOException $ex) {
                                                $this_function = __FUNCTION__;
                                                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                                echo(" Query = $query<BR> \n");
                                                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                                //exit();
                                            }

                                            // Save the mark
                                        }
                                    }
                                }

                            }

                        }
                        // Put the columns in an array and get the iqr data
                        // put this data in the module names table
                        /*
                        $module_iqrs = array();
                        $module_means = array();
                        for ($col = $first_mark_col; $col <= $last_mark_col; $col++)
                        {
                            $val = array();
                            $module_index = $col - $first_mark_col+1;
                            // do not add modules from outside the school
                            // i.e. not in the modules file
                            if($module_index <= $num_modules) {
                                for($row = $first_mark_col ; $row < $last_data_row ; $row++) {
                                    $mark = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                                    if (is_numeric($mark)) {
                                        $val[] = $mark;
                                    }
                                }
                                // Get the iqr data
                                $iqr = BoxPlot5Stats($val);
                                $min = $iqr[0];
                                $q25 = $iqr[1];
                                $q50 = $iqr[2];
                                $q75 = $iqr[3];
                                $max = $iqr[4];
                                $mean = Average($val);
                                $module_iqrs[$module_index-1] = $iqr;
                                $module_means[$module_index-1] = [ $module_index-1, $mean ];

                                // enter the iqr data into the table
                                $query = "UPDATE $module_names_table SET min='$min', max='$max', mean='$mean', q25='$q25', q50='$q50', q75='$q75' WHERE id='$module_index'";
                                try {
                                    $results = $db->query($query);
                                } catch (PDOException $ex) {
                                    $this_function = __FUNCTION__;
                                    echo "An Error occured accessing the database in function: $this_function <BR>\n";
                                    echo(" Query = $query<BR> \n");
                                    echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                                    //exit();
                                }

                                // Save the mark
                            }
                        }*/
                    }
                    // added the marks put this in the db
                    // Add these to the table
                    // Only add if not in the table
                    // need to get it from the table
                    $query = "SELECT * FROM $marks_index WHERE year_begin='$year_begin' AND level='$level' AND version='$version' ";
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
                        // do not add
                        echo("Not adding to index as already exists.<BR> Query = $query<BR>\n");
                    } else {
                        $query = "INSERT $marks_index set year_begin='$year_begin', year_end='$year_end', level='$level', version='$version',
                            got_summary_table='1', got_modules_table='1' ";
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
                } else {
                    $display_block .= "<H3 style='color: red; background-color: #f1c40f'>You need to browse and select TWO xls files to upload.</H3>";
                }
            }
        //}// file name do no match year/level/version
    } elseif ($POST_OP == "download_csv") {

    }

//---------------------------------------------------------------------------------
    function rand_n($min, $max, $num)
    {
        $rand_nums = array();
        for ($i = 0; $i < $num; $i++) {
            //$rand_nums[]=mt_rand($min,$max);
            $rand_nums[] = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        }
        return $rand_nums;
    }

    $y0 = rand_n(700, 1000, 25);
    $y1 = rand_n(0.5, 3.5, 20);

    $y0 = BoxPlot5Stats($y0);
    for ($i = 0; $i < count($y0); ++$i) {
        $y0[$i] = (int)$y0[$i];
    }
    $y0_avr = Average($y0);
    $y0_mark = mt_rand(min($y0), max($y0));
    $display_block = "<div id='container' style='height: 580px; border-style: solid; border-width: 1px; border-color: black; padding: 5px;'></div>\n";
//---------------------------------------------------------------------------------
    $display_block .= "<hr><h2>Upload attendance sheet xls files... </H2>\n";
    $display_block .= "<h3>Either the mech format or the Civil format</h3>\n";
    $display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\" enctype=\"multipart/form-data\">\n";
    $display_block .= "<P> Select the attendance record csv file </P>\n";
    $display_block .= "<P><input name=\"attend_xls\" type=\"file\" id=\"attend_xls\" /></P>\n";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"upload_xls\">\n";

    $display_block .= "<P><input name='show_table_chk' type='checkbox' id='show_table_chk' value='1'/>Show loaded data table.</P>\n";

    $display_block .= "<p><input type=\"submit\" id=\"submitbutton_xls\" name=\"submit\" value=\"Upload the csv file of attendance records\"></p>\n";

    $display_block .= "</form>\n";

}

?>
<HTML>
<?php
(@include_once("./header.php")) OR die("Cannot find this file to include: header.php<BR>");
?>

<BODY>
<div id="wrapper">
    <div id="header">
        <span class="header"><img src="./images/chart_icon_negate_36.png" height=30px
                                  align="middle"><?php echo(" $running_title - $running_subtitle") ?></span>
    </div><!-- /header -->

    <div id="content">
        <?php echo $display_block; ?>
    </div><!-- /content -->

    <div class="footer_link" id="footer">
        <P>Return to the
            <a href="./index.php">Index page</a> -
            <a href="./admin_index.php?pm_id=<?php echo("$pm_id") ?>">Mark year admin page</a> -
            <a href="./admin_index.php">Main admin page</a>.
        </P>
    </div><!-- /footer -->

</div><!-- /wrapper -->


<!--Body content-->

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>


<script type="text/javascript">
    var chart;
    var my_boxplots = {

        chart: {
            renderTo: 'container',
            type: 'boxplot'
        },

        title: {
            text: 'Module mark boxplots for Joe Bloggs',
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        },
        /*
         subtitle: {
         text: 'Level 2, 2016. ',
         style: {
         fontSize: '12px',
         fontFamily: 'Verdana, sans-serif'
         }
         },
         */

        legend: {
            enabled: false
        },

        xAxis: {
            categories: ['CIVE1', 'CIVE2', 'CIVE3', 'CIVE4', 'CIVE5'],
            //title: {
            //    text: 'Module name'
            //},
            labels: {
                rotation: -45,
                style: {
                    fontSize: '8px',
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },

        yAxis: {
            title: {
                text: 'Module mark (%)'
            },
            max: 100,
            minRange: 0,
            min: 0,
            /*
             plotLines: [{
             value: 50,
             color: 'red',
             width: 1,
             label: {
             text: 'Year \nmark',
             align: 'right',
             style: {
             color: 'gray'
             }
             }

             }],
             */
            plotBands: [{
                color: 'rgba(255,204,204,0.3)', // Color value
                from: 55, // Start of the plot band
                to: 65, // End of the plot band
                label: {
                    text: 'target mean',
                    align: 'left',
                    verticalAlign: 'top',
                    rotation: 90,
                    style: {
                        color: 'gray'
                    }
                }
            }]
        },

        plotOptions: {
            boxplot: {
                fillColor: '#F0F0E0',
                lineWidth: 2,
                medianColor: '#0C5DA5',
                medianWidth: 3,
                stemColor: '#A63400',
                stemDashStyle: 'dot',
                stemWidth: 1,
                whiskerColor: '#3D9200',
                whiskerLength: '20%',
                whiskerWidth: 3
            }
        },

        series: [{
            name: 'Module marks',
            data: [
                <?php echo json_encode($y0) ?>,
                [733, 853, 939, 980, 1080],
                [714, 762, 817, 870, 918],
                [724, 802, 806, 871, 950],
                [834, 836, 864, 882, 910]
            ],
            tooltip: {
                headerFormat: '<em>Exam: {point.key}</em><br/>'
            }
        }, {
            name: 'Mean',
            color: Highcharts.getOptions().colors[0],
            type: 'scatter',
            data: [ // x, y positions where 0 is the first category
                [0, 50],
                [4, 55]
            ],
            marker: {
                fillColor: '#0C5DA5',//as median or 'red',
                lineWidth: 1,
                lineColor: Highcharts.getOptions().colors[0],
                symbol: 'square'
            },
            tooltip: {
                pointFormat: '{point.y:.1f}'//one dp
            }
        }],

        // remove the highcharts.com link
        credits: {
            enabled: false
        }


    };

    //my_boxplots.series[0].data = traffic;
    var my_means = <?php echo json_encode($module_means) ?>;
    var arrayLength = my_means.length;
    for (var i = 0; i < arrayLength; i++) {
        //alert(my_means[i]);
        //Do something
    }
    // Chart data
    my_boxplots.series[0].data = <?php echo json_encode($module_iqrs) ?>;
    // spot data
    my_boxplots.series[1].data = <?php echo json_encode($module_means) ?>;
    my_boxplots.xAxis.categories = <?php echo json_encode($module_summary_names) ?>;
    my_boxplots.title.text = <?php echo json_encode($plot_title) ?>;

    chart = new Highcharts.Chart(my_boxplots);

</script>

</BODY>
</HTML>
