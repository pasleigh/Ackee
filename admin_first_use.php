<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 09/07/2016
 * Time: 15:56
 */
$first_use = true;
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./connect_pdo.php")) OR die("Cannot find this file to include: connect_pdo.php<BR>");
//(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./table_functions.php")) OR die("Cannot include table_functions.php<BR>");

if (array_key_exists('op', $_POST) == false) {
    $_POST['op'] = null;
}
// if the form was submitted then enter it all into the database
//echo("OP=$_POST['op'] . <BR>\n");
if (array_key_exists('op', $_POST)) {
    $POST_OP = $_POST['op'];
} else {
    $POST_OP = "";
}
//echo("POST_OP=$POST_OP <BR>\n");

// see all the POST values
//foreach ($_POST as $key => $value){
//  echo "{$key} = {$value}<BR>\r\n";
//}

$display_block = "";

// Does the definition table exist?
$definition_table_name = "definition";
try{
    $db->query("DESCRIBE $definition_table_name");
}catch (PDOException $ex) {
    //echo "$definition_table_name doesn't exist in database $dbName\n";
    //
    // Create it
    $create_table_query = "
                --
                -- Table structure for table `$definition_table_name`
                --

                CREATE TABLE IF NOT EXISTS `$definition_table_name` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `school_short_name` varchar(10) NOT NULL,
                  `school_title` varchar(50) NOT NULL,
                  `default_module_prefix` varchar(5) NOT NULL,
                  `header_image_name` varchar(50) NOT NULL,
                  `running_title` varchar(50) NOT NULL,
                  `running_subtitle` varchar(50) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            ";

    $initial_data_query = "INSERT INTO `$definition_table_name` (`id`, `school_short_name`, `school_title`, `default_module_prefix`, `header_image_name`, `running_title`, `running_subtitle`) VALUES
            (1, 'civil', 'Civil Engineering', 'CIVE', 'leeds_eng_header.png', 'AcKee', 'View Attendance Records');";

    try {
        $setup = $db->prepare($create_table_query);
        $setup->execute();

        $setup = $db->prepare($initial_data_query);
        $setup->execute();
        $display_block .= "$definition_table_name table created<BR>\n";
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo("An Error occured accessing the database in function: $this_function <BR>\n");
        echo("Unable to update data to the table $staff_names_table<BR>\n");
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }

    // Probably the params file does not exist
    // Check
    $table_name = "params";
    try{
        $db->query("DESCRIBE $table_name");
    }   catch (PDOException $ex) {     // Does not exist
        // Create it
        create_params_table_structure($db);
        $display_block .= "$table_name table created<BR>\n";
    }
    // Put some temporary data in the params table
    $query = "INSERT INTO $table_name (year_begin,year_end) VALUES (2015,2016)  ";
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo "An Error occured!<BR>\n";
        echo(" Unable to insert data to the table $table_name . <BR>Query: $query");
        $err_msg = $ex->getMessage();
        echo("$err_msg <BR>\n");
    }

    // Probably the marks_index table does not exist
    // Check
    $table_name = "marks_index";
    try{
        $db->query("DESCRIBE $table_name");
    }   catch (PDOException $ex) {     // Does not exist
        // Create it
        create_marks_index_table_structure($db);
        $display_block .= "$table_name table created<BR>\n";
    }


    // Probably the categories file does not exist
    // Check
    $table_name = "categories";
    try{
        $db->query("DESCRIBE $table_name");
    }   catch (PDOException $ex) {     // Does not exist
        // Create it
        create_categories_table_structure($db);
        $display_block .= "$table_name table created<BR>\n";
    }



    // Probably the tutor_initials file does not exist
    // Check
    $table_name = "tutor_initials";
    try{
        $db->query("DESCRIBE $table_name");
    }   catch (PDOException $ex) {     // Does not exist
        // Create it
        create_tutor_initials_table_structure($db);
        $display_block .= "$table_name table created<BR>\n";
    }


    // Probably the students table does not exist
    // Check
    $table_name = "student_names";
    try{
        $db->query("DESCRIBE $table_name");
    }   catch (PDOException $ex) {     // Does not exist
        // Create it
        create_students_table_structure($db);
        $display_block .= "$table_name table created<BR>\n";
    }

    // Probably the staff_names table does not exist
    // Check
    $table_name = "staff_names";
    try{
        $db->query("DESCRIBE $table_name");
    }   catch (PDOException $ex) {     // Does not exist
        // Does not exist
        // Create it
        create_staff_names_table_structure($db);
        $display_block .= "$table_name table created<BR>\n";
    }

    // probably the admin users does not exist
    create_admin_users_table_structure($db);
    $display_block .= "Admin users table created<BR>\n";

}


$display_block .= "<h1>Edit details for your School and yourself - the first member of staff to be entered in the database</h1>";
$staff_names_table = "staff_names";
$admin_users_table = "admin_users";

//$display_block = "<P>Database definitions table: $definition_table_name</P>";

if ($POST_OP == "") {
    $display_block .= "<h2>School details</h2>";

    $display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">";

    $display_block .= "<table border='0'>\n";
    $display_block .= "<TR>\n";
    $display_block .= "<TD>School name in title: (e.g. Civil Engineering) </TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"school_title\"  value=\"Civil Engineering\" size=30 maxlength=50></TD>\n";
    $display_block .= "</TR>\n";
    $display_block .= "<TR>\n";
    $display_block .= "<TD>School short name: (e.g. civil) </TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"school_short_name\" value=\"civil\" size=10 maxlength=10></TD>\n";
    $display_block .= "</TR>\n";
    $display_block .= "<TR>\n";
    $display_block .= "<TD>School default module code: (e.g. CIVE) </TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"school_module_code\" value='CIVE' size=5 maxlength=5></TD>\n";
    $display_block .= "</TR>\n";
    $display_block .= "<TR>\n";
    $display_block .= "<TD>Headline name of this system: (e.g. AcKee) </TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"school_running_title\" value='AcKee' size=30 maxlength=50></TD>\n";
    $display_block .= "</TR>\n";
    $display_block .= "<TD>Running sub-title of this system: (e.g. Attendance Record Viewing) </TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"school_running_subtitle\" value='Attendance Record Viewing' size=30 maxlength=30></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "</table>\n";

    $display_block .= "<h2>Your details</h2>";

    $display_block .= "<table>\n";

    $display_block .= "<TR>\n";
    $display_block .= "<TD>Title</TD>\n";
    $display_block .= "<TD><select name=\"title_select\">\n";
    $selected = "";
    $display_block .= "<option value=\"Prof\" $selected>Prof</option>\n";
    $selected = "selected";
    $display_block .= "<option value=\"Dr\" $selected>Dr</option>\n";
    $selected = "";
    $display_block .= "<option value=\"Mrs\" $selected>Mrs</option>\n";
    $display_block .= "<option value=\"Ms\" $selected>Ms</option>\n";
    $display_block .= "<option value=\"Miss\" $selected>Miss</option>\n";
    $display_block .= "<option value=\"Mr\" $selected>Mr</option>\n";
    $display_block .= "</select></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "<TR>\n";
    $display_block .= "<TD>Initials you could be refered to <BR>- e.g. Tom Selwyn Jones might be TSJ</TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"initials\" value='PAS' size=10 maxlength=30></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "<TR>\n";
    $display_block .= "<TD>First name</TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"firstname\" value='Andy' size=30 maxlength=30></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "<TR>\n";
    $display_block .= "<TD>Family name</TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"familyname\" value='Sleigh' size=30 maxlength=40></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "<TR>\n";
    $display_block .= "<TD>email</TD>\n";
    $display_block .= "<TD><input type=\"text\" name=\"email\" value='p.a.sleigh@leeds.ac.uk' size=30 maxlength=40></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "<TR>\n";
    $display_block .= "<TD>username</TD>\n";
    $username = $_SERVER['REMOTE_USER'];
    $display_block .= "<TD><input type=\"text\" name=\"username\" value='$username' size=30 maxlength=40></TD>\n";
    $display_block .= "</TR>\n";

    $display_block .= "</table>\n";

    $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"submit_new\">";

    $display_block .= "<p><input type=\"submit\" id=\"submitbutton_new\" name=\"submit\" value=\"Submit these details.\"></p>";


    $display_block .= "</form>";


    $display_block .= "<hr>\n";
} elseif ($POST_OP == "submit_new") {

    $school_title = $_POST['school_title'];
    $school_module_code = $_POST['school_module_code'];
    $school_short_name = $_POST['school_short_name'];
    $school_running_title = $_POST['school_running_title'];
    $school_running_subtitle = $_POST['school_running_subtitle'];
    //$header_image = $_POST['header_image'];
    /*
        $initial_data_query = "INSERT INTO `$definition_table_name` ( `school_short_name`, `school_title`, `default_module_prefix`, `header_image_name`, `running_title`) VALUES
                ($school_short_name', '$school_title', '$school_module_code', '$header_image', '$school_running_title');";
    */
    $initial_data_query = "UPDATE $definition_table_name SET school_short_name='$school_short_name', school_title='$school_title',
                              default_module_prefix='$school_module_code',
                              running_title='$school_running_title'
                              WHERE id=1";
    //$display_block .= $query;
    try {
        $db->query($initial_data_query);

        $display_block .= "Submitted: <BR><BR>\n";
        $display_block .= "School short name: $school_short_name<BR>\n";
        $display_block .= "School title: $school_title <BR>\n";
        $display_block .= "Headline name: $school_running_title<BR>\n";
        $display_block .= "Running sub-title: $school_running_subtitle<BR>\n";
        $display_block .= "Default module code: $school_module_code<BR>\n";

    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $initial_data_query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }

    //$staffid = $_POST['staffid'];
    $title = $_POST['title_select'];
    $initials = $_POST['initials'];
    $firstname = $_POST['firstname'];
    $familyname = $_POST['familyname'];
    $fullname = "$firstname $familyname";
    $email = $_POST['email'];
    $username = $_POST['username'];
    $inactive = 0;

    $query = "INSERT INTO $staff_names_table (initials,title,firstname,familyname,fullname,email,username,inactive,admin_level) VALUES ('$initials','$title','$firstname','$familyname','$fullname','$email','$username','$inactive',3)  ";
    //$display_block .= $query;
    try {
        $db->query($query);
        $display_block .= "Submitted: <BR><BR>\n";

        $display_block .= "Title: $title <BR>\n";
        $display_block .= "Full Name: $fullname <BR>\n";
        $display_block .= "First name: $firstname<BR>\n";
        $display_block .= "Family Name: $familyname<BR>\n";
        $display_block .= "Reference Initials: $initials<BR>\n";
        $display_block .= "Email: $email<BR>\n";
        $display_block .= "username: $username<BR>\n";
        $display_block .= "Inactive flag: $inactive  <BR>\n";
    } catch (PDOException $ex) {
        echo "An Error occured!<BR>\n";
        echo(" Unable to insert data to the table $staff_names_table . <BR>Query: $query");
        $err_msg = $ex->getMessage();
        echo("$err_msg <BR>\n");
    }

    // And make this user a super admin
    $query = "INSERT INTO $admin_users_table (firstname,familyname,fullname,email,username,admin_level) VALUES ('$firstname','$familyname','$fullname','$email','$username','3')  ";
    //$display_block .= $query;
    try {
        $db->query($query);
    } catch (PDOException $ex) {
        echo "An Error occured!<BR>\n";
        echo(" Unable to insert data to the table $admin_users_table . <BR>Query: $query");
        $err_msg = $ex->getMessage();
        echo("$err_msg <BR>\n");
    }

    $display_block .= "<BR><hr>\n";

    $display_block .= "<P>If the above looks right, let's go on to enter some data  </P><P><A href=\"./admin_index.php\">Admin index</A></P><BR><hr>\n";
}// end of if ($POST_OP == "edit")


?>
<HTML>
<?php
(@include_once("./header.php")) OR die("Cannot find this file to include: ./header.php<BR>");
?>

<BODY>
<div id="wrapper">
    <div id="header">
        <span class="header">First use details</span>
    </div><!-- /header -->

    <div id="content">
        <?php echo $display_block; ?>
    </div><!-- /content -->

    <div id="footer">
        <!--<P>Return to the
            <a href="./index.php">Admin index page</a>.-->
    </div><!-- /footer -->

</div><!-- /wrapper -->
</BODY>
</HTML>
