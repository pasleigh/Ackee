<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 24/06/2015
 * Time: 17:01
 */
function GetStudentListTable($db, $project_allocated, $students_table, $staff_table)
{
    $select_all_block = "<div>Select/De-select all<input type=\"checkbox\" id=\"select-all\" name=\"selectAll\" checked value=\"\"/></div>\n";
    $display_block = "\n";
    $display_block = "<div id=\"students_select_table\">\n";
    $display_block .= "<table id=\"student_email_list\" class=\"hover compact cell-border\" cellspacing=\"0\" width=\"100%\">\n";
    $display_block .= "
    <thead>
      <tr>
        <th>Student name</th>
        <th>SID</th>
        <th>Username</th>
        <th>Project ID</th>
        <th>Project Title</th>
        <th>Supervisor</th>
        <th>Second marker</th>
        <th>Ticked = Send email if marking is complete $select_all_block</th>
      </tr>
    </thead>
        ";
    $display_block .= "<tbody>\n";

    // list all the students
    $query = "select * from $project_allocated order by projectid";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo("An Error occured accessing the database in function: $this_function <BR>\n");
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }
    $rows = $results->fetchAll();
    foreach ($rows as $recs) {
        $project_id = $recs['projectid'];
        # $project_master_id = $recs['masterid'];
        $sid = $recs['sid'];
        $inactive = $recs['inactive'];
        $supervisor_id = $recs['supervisor_id'];
        $second_marker_id = $recs['second_marker_id'];
        $project_title = $recs['title'];

        if ($inactive != 1) {

            $markers = array();
            $markers_id = array();
            for ($i = 1; $i <= 5; $i++) {
                $markers[$i] = array();
                $markers_id[$i] = array();
                for ($j = 1; $j <= 3; $j++) {
                    $marker_entry = "marker_p$i" . "m$j" . "_id";
                    $marker_id = $recs[$marker_entry];
                    $markers_id[$i][$j] = $marker_id;
                    $markers[$i][$j] = GetStaffNameFromID($db, $marker_id, $staff_table, NULL);
                    # $name = $markers_id[$i][$j];
                    //echo("markers_id = ($i,$j) $name $marker_id<BR>");
                }
            }

            // Get the student name from SID
            $query = "select * from $students_table where sid = $sid";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo("An Error occured accessing the database in function: $this_function <BR>\n");
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            $recs = $results->fetch();
            $student_name = $recs['name'];
            $username = $recs['username'];
            # $programme = $recs['programme'];
            # $sel = $recs['sel'];

            $display_block .= "<tr>\n";
            $display_block .= "  <td>$student_name </td>\n";
            $display_block .= "  <td>$sid</td>\n";
            $display_block .= "  <td>$username</td>\n";
            $display_block .= "  <td>$project_id</td>\n";
            $display_block .= "  <td>$project_title</td>\n";
            $supervisor_name = GetStaffNameFromID($db, $supervisor_id, $staff_table, NULL);
            $display_block .= "  <td>$supervisor_name</td>\n";
            $second_marker_name = GetStaffNameFromID($db, $second_marker_id, $staff_table, NULL);
            $display_block .= "  <td>$second_marker_name</td>\n";
            $display_block .= "  <td style=\"text-align: center;\"><input type=\"checkbox\" name=\"check_$sid\" checked></td>\n";
            $display_block .= "</tr>\n";
        }
    }
    $display_block .= "</tbody>\n";
    $display_block .= "</table>\n";
    $display_block .= "</div>\n";
    return $display_block;
}

function delete_marks_tables_for_year($db, $year_end, $level, $version, $marks_index_table)
{
    // delete all tables for this year
    // a: module tables
    // b: mark - names table
    // c: mark - summary table
    // and entry in the mars_index table
    $display_block = "";


    $mark_names_table = GetModuleNamesTableName($year_end, $level, $version);
    $marks_summary_table = GetSummaryMarksTableName($year_end, $level, $version);

    // a: module tables
    $query = "SELECT * FROM $mark_names_table WHERE 1";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create marks table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }

    $recs = $results->fetchAll();
    foreach ($recs as $rec) {
        $module_code = $rec['module_code'];
        $module_table = GetModuleTableName($year_end, $module_code, $version);
        // delete this table
        $query = "DROP TABLE $module_table";
        $display_block .= "<span style='font-size: large; color: black'>Module $module_table: </span>";
        try {
            $results = $db->query($query);
            $display_block .= " <span style='font-size: large; color: red'> deleted </span><BR>\n";
        } catch (PDOException $ex) {
            $display_block .= " Err message: " . $ex->getMessage() . "<BR>\n";
        }
    }

    // b: mark - names table
    // delete the list of modules and module marks
    $query = "DROP TABLE $mark_names_table";
    $display_block .= "<span style='font-size: large; color: black'>Module names/stats table entry: $mark_names_table </span>";
    try {
        $results = $db->query($query);
        $display_block .= "<span style='font-size: large; color: red'> deleted </span><BR>";
    } catch (PDOException $ex) {
        $display_block .= " Query: $query<BR>\n";
        $display_block .= " Err message: " . $ex->getMessage() . "<BR>\n";
    }

    // c: mark - summary table
    // delete the list of student marks
    $query = "DROP TABLE $marks_summary_table";
    $display_block .= "<span style='font-size: large; color: black'>Module students marks summary table entry: $marks_summary_table </span>";
    try {
        $results = $db->query($query);
        $display_block .= "<span style='font-size: large; color: red'> deleted </span><BR>";
    } catch (PDOException $ex) {
        $display_block .= " Query: $query<BR>\n";
        $display_block .= " Err message: " . $ex->getMessage() . "<BR>\n";
    }

    // and entry in the mars_index table
    // delete the entry
    $query = "DELETE FROM $marks_index_table WHERE year_end='$year_end' AND version='$version' AND level='$level'";
    $display_block .= "<span style='font-size: large; color: black'>Projects $marks_index_table table entry: </span>";
    try {
        $results = $db->query($query);
        $display_block .= "<span style='font-size: large; color: red'> deleted </span><BR>";
    } catch (PDOException $ex) {
        $display_block .= " Query: $query<BR>\n";
        $display_block .= " Err message: " . $ex->getMessage() . "<BR>\n";

    }

    return $display_block;
}

function create_params_table_structure($db)
{

    $query = <<<SQL
    --
    -- Table structure for table `params`
    --
    CREATE TABLE `params` (
      `id` int(11) NOT NULL,
      `year_begin` year(4) NOT NULL,
      `year_end` year(4) NOT NULL,
      `term_1_sunday_start` DATETIME DEFAULT NULL,
      `term_1_num_weeks` int(11) DEFAULT 0,
      `term_2_sunday_start` DATETIME DEFAULT NULL,
      `term_2_num_weeks` int(11) DEFAULT 0,
      `term_3_sunday_start` DATETIME DEFAULT NULL,
      `term_3_num_weeks` int(11) DEFAULT 0,
      `num_modules` int(11) DEFAULT '0',
      `num_years` int(11) DEFAULT '0',
      `num_students` int(11) DEFAULT '0',
      `visible` int(1) DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SQL;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create params table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetSummaryMarksTableName($year_end, $level, $version = 1)
{
    $year_begin = $year_end - 1;
    $year_end = $year_end - 2000;
    $table_name = "marks_" . $year_begin . "_" . $year_end . "_lev$level" . "_summary" . "_v$version";

    return $table_name;
}

function create_summary_marks_table_structure($db, $year_end, $level, $version, $num_modules, &$table_name)
{

    //$year_begin = $year_end - 1;
    //$year_end = $year_end - 2000;
    //$table_name = "marks_" . $year_begin . "_" . $year_end . "_lev$level" . "_summary" . "_v$version";

    $table_name = GetSummaryMarksTableName($year_end, $level, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(10) NOT NULL,
      `name` char(255) DEFAULT NULL,
      `tutor` char(5) DEFAULT NULL,
      `level1_avg` float DEFAULT NULL,
      `level1_credits` int(3) DEFAULT NULL,
      `level2_avg` float DEFAULT NULL,
      `level2_credits` int(3) DEFAULT NULL,
      `level3_avg` float DEFAULT NULL,
      `level3_credits` int(3) DEFAULT NULL,
      `level4_avg` float DEFAULT NULL,
      `level4_credits` int(3) DEFAULT NULL,
      `level5_avg` float DEFAULT NULL,
      `level5_credits` int(3) DEFAULT NULL,
      `decision` char(20) DEFAULT NULL,
      `classification` char(20) DEFAULT NULL,
      `notes` varchar(255) DEFAULT NULL,
SQL;
    $query_mid = "";
    for ($i = 1; $i <= $num_modules; $i++) {
        $query_mid .= "m$i float DEFAULT NULL, ";
    }

    $query_end = <<<SQL_END
      PRIMARY KEY (`id`),
      UNIQUE (`sid`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL_END;

    $query .= $query_mid . $query_end;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create module summary marks table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetModuleNamesTableName($year_end, $level, $version = 1)
{
    $year_begin = $year_end - 1;
    $year_end_short = $year_end - 2000;
    $table_name = "marks_" . $year_begin . "_" . $year_end_short . "_lev$level" . "_names" . "_v$version";

    return $table_name;
}

function create_module_names_table_structure($db, $year_end, $level, $version, &$table_name)
{

    //$year_begin = $year_end - 1;
    //$year_end_short = $year_end - 2000;
    //$table_name = "marks_" . $year_begin . "_" . $year_end_short . "_lev$level" . "_names" . "_v$version";

    $table_name = GetModuleNamesTableName($year_end, $level, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `full_name` char(255) DEFAULT NULL,
      `summary_name` char(255) DEFAULT NULL,
      `module_code` char(255) NOT NULL,
      `credits` int DEFAULT NULL,
      `min` float DEFAULT NULL,
      `max` float DEFAULT NULL,
      `q25` float DEFAULT NULL,
      `q50` float DEFAULT NULL,
      `q75` float DEFAULT NULL,
      `mean` float DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE (`module_code`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create module_names table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetModuleTableName($year_end, $module_code, $version = 1)
{
    $year_begin = $year_end - 1;
    $year_end = $year_end - 2000;
    $module_code = strtolower($module_code);

    $table_name = "mod_" . $year_begin . "_" . $year_end . "_$module_code" . "_$version";

    return $table_name;
}

function create_module_table_structure($db, $year_end, $module_code, $version, &$table_name)
{
// NEED version/resit in the header
    //$year_begin = $year_end - 1;
    //$year_end = $year_end - 2000;
    //$module_code = strtolower($module_code);
    //$table_name = "mod_" . $year_begin . "_" . $year_end . "_$module_code";
    $table_name = GetModuleTableName($year_end, $module_code, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(10) NOT NULL,
      `mark` float DEFAULT NULL,
      `p1` float DEFAULT NULL,
      `p2` float DEFAULT NULL,
      `p3` float DEFAULT NULL,
      `p4` float DEFAULT NULL,
      `p5` float DEFAULT NULL,
      `p6` float DEFAULT NULL,
      `p7` float DEFAULT NULL,
      `p8` float DEFAULT NULL,
      `p9` float DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE (`sid`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create module_names table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function create_categories_table_structure($db)
{

    $query = <<<SQL
    --
    -- Table structure for table `params`
    --

    CREATE TABLE IF NOT EXISTS `categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
      `category` varchar(50) NOT NULL,
      `active` int(1) DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

SQL;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create categories table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function create_tutor_initials_table_structure($db)
{

    $query = <<<SQL
    --
    -- Table structure for table `params`
    --

    CREATE TABLE IF NOT EXISTS `tutor_initials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
      `initials` varchar(50) NOT NULL,
      `staff_id` int(3) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

SQL;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create categories table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}


function create_admin_users_table_structure($db)
{

    $query = <<<SQL
    --
    -- Table structure for table `admin_users`
    --

    CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
      `firstname` varchar(25) DEFAULT NULL,
      `familyname` varchar(50) DEFAULT NULL,
      `fullname` varchar(75) DEFAULT NULL,
      `email` varchar(150) DEFAULT NULL,
      `username` varchar(25) DEFAULT NULL,
      `admin_level` int(1) DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

SQL;
    try {
        $db->query($query);
        //echo("$query<BR>\n");
    } catch (PDOException $ex) {
        echo("Unable to create admin_users table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
    //echo("Leaving create admin table<BR>\n");

}

function create_staff_names_table_structure($db)
{

    $query = <<<SQL
    --
    -- Table structure for table `staff_names`
    --

    CREATE TABLE IF NOT EXISTS `staff_names` (
    `staffid` int(11) NOT NULL AUTO_INCREMENT,
      `initials` varchar(9) DEFAULT NULL,
      `title` varchar(10) DEFAULT NULL,
      `firstname` varchar(25) DEFAULT NULL,
      `familyname` varchar(50) DEFAULT NULL,
      `fullname` varchar(75) DEFAULT NULL,
      `email` varchar(150) DEFAULT NULL,
      `username` varchar(25) NOT NULL,
      `room` varchar(10) DEFAULT NULL,
      `inactive` int(1) DEFAULT '0',
      `admin_level` int(1) DEFAULT '0',
      PRIMARY KEY (`staffid`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

    ALTER TABLE `staff_names` ADD UNIQUE KEY `username` (`username`);

SQL;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create staff_names table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function create_students_table_structure($db)
{
    $query = <<<SQL
    --
    -- Table structure for table `students_meng2_2016`
    --

    CREATE TABLE `student_names` (
    `sid` char(10) NOT NULL,
      `name` char(255) DEFAULT NULL,
      `username` char(10) NOT NULL,
      `programme` char(64) DEFAULT NULL,
      `sel` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

SQL;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create staff_names table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }

}

function create_marks_index_table_structure($db)
{

    $query = <<<SQL
    --
    -- Table structure for table `params`
    --

    CREATE TABLE IF NOT EXISTS `marks_index` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
      `year_begin` year(4) NOT NULL,
      `year_end` year(4) NOT NULL,
      `level` int(4) NOT NULL,
      `version` int(4) NOT NULL,
      `got_summary_table` int(11) DEFAULT '0',
      `got_modules_table` int(11) DEFAULT '0',
      `visible` int(1) DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

SQL;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create marks_index table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetStudentListTableName($school_short_name, $year, $version)
{
    $table_name = "student_list_" . $school_short_name . "_" . $year . "_v" . $version;
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_student_list_table_structure($db, $school_short_name, $year, $version = 1, &$table_name)
{

    $table_name = GetStudentListTableName($school_short_name, $year, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(255) DEFAULT NULL,
      `family_name` char(255) DEFAULT NULL,
      `first_name` char(255) DEFAULT NULL,
      `email` char(255) DEFAULT NULL,
      `level` INTEGER DEFAULT 0,
      `mean_attendance` float DEFAULT NULL,
      `mean_attendance_filtered` float DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}
/*
function GetStudentListFilteredTableName($school_short_name, $year, $version)
{
    $table_name = "student_list_filtered_" . $school_short_name . "_" . $year . "_v" . $version;
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_student_list_filtered_table_structure($db, $school_short_name, $year, $version = 1, &$table_name)
{

    $table_name = GetStudentListFilteredTableName($school_short_name, $year, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(255) DEFAULT NULL,
      `family_name` char(255) DEFAULT NULL,
      `first_name` char(255) DEFAULT NULL,
      `email` char(255) DEFAULT NULL,
      `level` INTEGER DEFAULT 0,
      `mean_attendance` float DEFAULT NULL,
      `mean_attendance_filtered` float DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}
*/
function GetStudentModuleTableName($school_short_name, $year, $version)
{
    $table_name = "student_module_list_" . $school_short_name . "_" . $year . "_v" . $version;
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_student_module_table_structure($db, $school_short_name, $year, $version = 1, &$table_name)
{

    $table_name = GetStudentModuleTableName($school_short_name, $year, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(255) DEFAULT NULL,
      `module_code` char(255) DEFAULT NULL,
      `module_table` char(255) DEFAULT NULL,
      `mean_module_attendance` float DEFAULT NULL,
SQL;

    for ($i = 0; $i <= 25; $i++) {
        $query .= "`w$i` float DEFAULT NULL,";
    }
    $query .= "PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";


    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetStudentModuleFilteredTableName($school_short_name, $year, $version)
{
    $table_name = "student_module_list_filtered_" . $school_short_name . "_" . $year . "_v" . $version;
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_student_module_filtered_table_structure($db, $school_short_name, $year, $version = 1, &$table_name)
{

    $table_name = GetStudentModuleFilteredTableName($school_short_name, $year, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(255) DEFAULT NULL,
      `module_code` char(255) DEFAULT NULL,
      `module_table` char(255) DEFAULT NULL,
      `mean_module_attendance` float DEFAULT NULL,
SQL;

    for ($i = 0; $i <= 25; $i++) {
        $query .= "`w$i` float DEFAULT NULL,";
    }
    $query .= "PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";


    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetStudentModuleFilteredSmallTableName($school_short_name, $year, $version)
{
    $table_name = "student_module_list_filtered_small_" . $school_short_name . "_" . $year . "_v" . $version;
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_student_module_filtered_small_table_structure($db, $school_short_name, $year, $version = 1, &$table_name)
{

    $table_name = GetStudentModuleFilteredSmallTableName($school_short_name, $year, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sid` char(255) DEFAULT NULL,
      `module_code` char(255) DEFAULT NULL,
      `module_table` char(255) DEFAULT NULL,
      `mean_module_attendance` float DEFAULT NULL,
SQL;

    for ($i = 0; $i <= 25; $i++) {
        $query .= "`w$i` float DEFAULT NULL,";
    }
    $query .= "PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";


    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetModuleAttendTableName($school_short_name, $year, $module_code, $version)
{
    $table_name = "mod_" . $school_short_name . "_" . $module_code . "_" . $year . "_v" . $version;
    $table_name = strtolower($table_name);
    $table_name = str_replace(" ", "_", $table_name);
    $table_name = str_replace("-", "_", $table_name);
    $table_name = str_replace("*", "", $table_name);
    $table_name = str_replace("personal_tutorial_", "", $table_name);
    return $table_name;
}

function GetModuleRawAttendTableName($school_short_name, $year, $module_code, $version)
{
    $table_name = GetModuleAttendTableName($school_short_name, $year, $module_code, $version);
    $table_name = "raw_" . $table_name;
    return $table_name;
}

function create_module_attend_activities_table_structure($db, $school_short_name, $year, $module_code, $version = 1, &$table_name)
{

    $table_name = GetModuleAttendTableName($school_short_name, $year, $module_code, $version);

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `activity` char(255) DEFAULT NULL,
      `date_original` char(255) DEFAULT NULL,
      `start_time_original` char(255) DEFAULT NULL,
      `module_code` char(255) DEFAULT NULL,
      `date_time_start` DATETIME NOT NULL,
      `date_time_end` DATETIME NOT NULL,
      `credits` int DEFAULT NULL,
      `number` INTEGER DEFAULT 0,
      `attend` INTEGER DEFAULT 0,
      `num_AA` INTEGER DEFAULT 0,
      `num_AD` INTEGER DEFAULT 0,
      `num_AR` INTEGER DEFAULT 0,
      `num_NR` INTEGER DEFAULT 0,
      `num_PB` INTEGER DEFAULT 0,
      `num_PR` INTEGER DEFAULT 0,
      `num_PS` INTEGER DEFAULT 0,
      `num_UA` INTEGER DEFAULT 0,
      `attendance` float DEFAULT NULL,
      `flag_low_numbers` INTEGER DEFAULT 0,
      `flag_low_attendance` INTEGER DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function create_module_raw_table_structure($db, $school_short_name, $year, $module_code, $version = 1, &$table_name, $delete = 0)
{

    $table_name = GetModuleRawAttendTableName($school_short_name, $year, $module_code, $version);

    if ($delete == 1) {
        $query = "SHOW TABLES LIKE '$table_name'";
        try {
            $results = $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();
        $num = $results->rowCount();
        if ($num > 0) {
            $query = "DROP TABLE $table_name";
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
    }

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `activity` char(255) DEFAULT NULL,
      `date_original` char(255) DEFAULT NULL,
      `start_time_original` char(255) DEFAULT NULL,
      `module_code` char(255) DEFAULT NULL,
      `date_time_start` DATETIME NOT NULL,
      `date_time_end` DATETIME NOT NULL,
      `sid` char(255) DEFAULT NULL,
      `family_name` char(255) DEFAULT NULL,
      `first_name` char(255) DEFAULT NULL,
      `attend_code1` char(255) DEFAULT NULL,
      `flagged` INTEGER DEFAULT 0,
      `flagged_small` INTEGER DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function create_definition_table_structure($db)
{
    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `definition` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `school_short_name` varchar(10) NOT NULL,
    `school_title` varchar(50) NOT NULL,
    `default_module_prefix` varchar(5) NOT NULL,
    `header_image_name` varchar(50) NOT NULL,
    `running_title` varchar(50) DEFAULT NULL,
    `running_subtitle` varchar(50) DEFAULT NULL,
    `year` INTEGER NOT NULL,
    `raw_data_issue` INTEGER NOT NULL,
    `raw_data_version` INTEGER NOT NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL;

    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetModuleSummaryStatsTableName($school_short_name, $year)
{
    // returns the name of the module summary table

    $table_name = "summary_$school_short_name" . "_$year";
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_module_stats_table_structure($db, $table_name, $delete = 0)
{

    if ($delete == 1) {
        $query = "SHOW TABLES LIKE '$table_name'";
        try {
            $results = $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();
        $num = $results->rowCount();
        if ($num > 0) {
            $query = "DROP TABLE $table_name";
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
    }

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
    `module_code` varchar(256) NOT NULL,
    `school_short_name` varchar(10) NOT NULL,
    `module_table` varchar(256) NOT NULL,
    `module_prefix` varchar(5) NOT NULL,
    `level` INTEGER NOT NULL,
      `credits` int DEFAULT NULL,
      `number_in_class` INTEGER DEFAULT 0,
      `number_of_activities` INTEGER DEFAULT 0,
      `attend` INTEGER DEFAULT 0,
      `num_AA` INTEGER DEFAULT 0,
      `num_AD` INTEGER DEFAULT 0,
      `num_AR` INTEGER DEFAULT 0,
      `num_NR` INTEGER DEFAULT 0,
      `num_PB` INTEGER DEFAULT 0,
      `num_PR` INTEGER DEFAULT 0,
      `num_PS` INTEGER DEFAULT 0,
      `num_UA` INTEGER DEFAULT 0,
      `min` float DEFAULT NULL,
      `max` float DEFAULT NULL,
      `q25` float DEFAULT NULL,
      `q50` float DEFAULT NULL,
      `q75` float DEFAULT NULL,
      `mean` float DEFAULT NULL,
SQL;

    for ($i = 0; $i <= 25; $i++) {
        $query .= "`w$i` float DEFAULT NULL,";
    }
    $query .= "PRIMARY KEY (`module_code`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function GetUniversalRawDataTableName($school_short_name, $year, $issue)
{
    // returns the name of the raw data table

    $table_name = "attend_uni_raw_$school_short_name" . "_$year" . "_i$issue";
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_universal_raw_table_structure($db, $school_short_name, $year, $issue, &$table_name, $delete = 0)
{

    $table_name = GetUniversalRawDataTableName($school_short_name, $year, $issue);

    if ($delete == 1) {
        $query = "SHOW TABLES LIKE '$table_name'";
        try {
            $results = $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();
        $num = $results->rowCount();
        if ($num > 0) {
            $query = "DROP TABLE $table_name";
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
    }

    $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `$table_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `activity` char(255) DEFAULT NULL,
      `date_original` char(255) DEFAULT NULL,
      `start_time_original` char(255) DEFAULT NULL,
      `module_code` char(255) DEFAULT NULL,
      `date_time_start` DATETIME NOT NULL,
      `date_time_end` DATETIME NOT NULL,
      `sid` char(255) DEFAULT NULL,
      `family_name` char(255) DEFAULT NULL,
      `first_name` char(255) DEFAULT NULL,
      `attend_code1` char(255) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
SQL;

    //echo("table name: $table_name<BR>");
    //echo("query: $query<BR>");
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}


function GetRawDataTableName($school_short_name, $year, $issue, $version)
{
    // returns the name of the raw data table

    $table_name = "attend_raw_$school_short_name" . "_$year" . "_i$issue" . "_v$version";
    $table_name = strtolower($table_name);
    return $table_name;
}

function create_raw_data_table_version($db, $table_name, $version)
{

    if ($version == 1) {
        // The original mech format - froma argos report
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `$table_name` (
          `PIDM_KEY` int(11) NOT NULL,
          `sid` int(11) NOT NULL,
          `student_family_name` text NOT NULL,
          `student_first_name` text NOT NULL,
          `session_years` text NOT NULL,
          `activity` text NOT NULL,
          `SWRACST_MOD_STATUS` text NOT NULL,
          `date` text NOT NULL,
          `start_time` text NOT NULL,
          `end_time` text NOT NULL,
          `SWBACTV_ATT_REC` text NOT NULL,
          `attend_code1` text NOT NULL,
          `attend_phrase1` text NOT NULL,
          `SWRACST_CONS_ABSENCE` int(11) NOT NULL,
          `SWRACST_OVER_ABSENCE` int(11) NOT NULL,
          `SWRACST_CONS_ABSENCE_2` int(11) NOT NULL,
          `SWRACST_OVER_ABSENCE_2` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL;
    } elseif ($version == 2) {
        // The original civil format - more raw data
        $query = <<<SQL
          CREATE TABLE IF NOT EXISTS `$table_name` (
          `activity` text NOT NULL,
          `date` text NOT NULL,
          `start_time` text NOT NULL,
          `end_time` text NOT NULL,
          `monitored` text NOT NULL,
          `attend_code1` text NOT NULL,
          `attend_code2` text NOT NULL,
          `sid` text NOT NULL,
          `student_name` text NOT NULL,
          `student_family_name` text NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL;

    }

    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table: $table_name. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function truncate_table($db, $table_name)
{
    $query = "TRUNCATE $table_name";
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo("Unable to create table. Query: $query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
    }
}

function delete_module_tables($db, $school_short_name, $delete)
{
    $response_text = "Deleting the module tables in 'delete_module_tables' function<BR>";

    if ($delete == 1) {
        $query = "SHOW TABLES LIKE 'mod_$school_short_name%'";
        $response_text = "$query<BR>";

        try {
            $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();

        foreach ($recs as $rec) {
            $table_name = $rec[0];
            $query = "DROP TABLE $table_name";
            //echo("$query<BR>");
            $response_text .= "$query<BR>";
            try {
                $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
        }

        $query = "SHOW TABLES LIKE 'raw_mod_$school_short_name%'";
        //echo("$query<BR>");
        $response_text .= "$query<BR>";
        try {
            $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();

        foreach ($recs as $rec) {
            $table_name = $rec[0];
            $query = "DROP TABLE $table_name";
            //echo("$query<BR>");
            $response_text .= "$query<BR>";
            try {
                $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
        }
    }else{
        $response_text .= "NOT deleted in delete_module_tables<BR>";
    }

    return $response_text;
}

function delete_student_list_tables($db, $school_short_name, $delete)
{
    $response_text = "Deleting the module tables in 'delete_student_list_tables' function<BR>";


    if ($delete == 1) {
        $query = "SHOW TABLES LIKE 'student_list_$school_short_name%'";
        $response_text .= "$query<BR>";
        try {
            $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();

        foreach ($recs as $rec) {
            $table_name = $rec[0];
            $query = "DROP TABLE $table_name";
            //echo("$query<BR>");
            $response_text .= "$query<BR>";
            try {
                $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
        }

        //=====================================================================================================
        $query = "SHOW TABLES LIKE 'student_module_list_$school_short_name%'";
        $response_text .= "$query<BR>";
        try {
            $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();

        foreach ($recs as $rec) {
            $table_name = $rec[0];
            $query = "DROP TABLE $table_name";
            $response_text .= "$query<BR>";
            //echo("$query<BR>");
            try {
                $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
        }

        //=====================================================================================================
        $query = "SHOW TABLES LIKE 'summary_$school_short_name%'";
        $response_text .= "$query<BR>";
        try {
            $db->query($query);
        } catch (PDOException $ex) {
            $this_function = __FUNCTION__;
            echo "An Error occured accessing the database in function: $this_function <BR>\n";
            echo(" Query = $query<BR> \n");
            echo(" Err message: " . $ex->getMessage() . "<BR>\n");
            exit();
        }
        $results = $db->query($query);
        $recs = $results->fetchAll();

        foreach ($recs as $rec) {
            $table_name = $rec[0];
            $query = "DROP TABLE $table_name";
            $response_text .= "$query<BR>";
            //echo("$query<BR>");
            try {
                $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
        }

    }else{
        $response_text .= "NOT deleted in delete_student_list_tables<BR>";
    }
    return $response_text;
}