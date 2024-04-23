<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 15/07/2016
 * Time: 16:15
 */


(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot read table_functions file<BR>");


   if(isset($_POST['get_pm_id']))
   {
       $pm_id = $_POST['get_pm_id'];
       $staff_id = $_POST['get_staff_id'];

       $query = "SELECT * FROM $staff_names WHERE staffid='$staff_id'";
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
       // tutor exists
       $rec = $results->fetch();
       $staff_fullname = $rec['fullname'];
       $staff_initials = $rec['initials'];


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
           $year_end = $recs['year_end'];
           $version = $recs['version'];
           $level = $recs['level'];
           $year_end_short = $year_end - 2000;


           $mark_summary_table = GetSummaryMarksTableName($year_end, $level, $version);
           // Get a list of students and put in the select list
           $query = "SELECT * FROM $mark_summary_table WHERE 1";
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
           //echo("<option selected disabled>Choose a student</option>\n";
           $response = "<option selected disabled>Choose a student</option>\n";
           $count = 0;
           foreach ($recs as $rec) {
               $student_name = $rec['name'];
               $sid = $rec['sid'];
               $id = $rec['id'];
               $tutor_initials = $rec['tutor'];
               $student_list_desc = "$student_name";

               if($tutor_initials == $staff_initials) {
                    $count += 1;
                   $response .= "<option value='$sid'>$student_list_desc</option>\n";
               }
           }
           if($count == 0){
               $response = "<option selected disabled>No tutee found for $staff_fullname ($staff_initials)</option>\n";
           }
       }else{
           $response = "<option selected disabled>No students found</option>\n";
       }

       echo($response);
       exit;
   }

?>

