<?PHP

function GetStaffNameFromID($db, $staff_id, $staff_table, $usetitle)
{
    $name = "";
    if ($staff_id == "") {
        return $name;
    }
//echo("Staff table $staff_table<BR>");
    $get_list_query = "select * from $staff_table where staffid=$staff_id";
    try {
        $get_list_res = $db->query($get_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from database. Query: $get_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return $name;
    }

    $rows = $get_list_res->fetchAll();
    //$get_list_res->closeCursor();
    foreach( $rows as $rec) {
        //$initials = $recs['initials'];
        $fullname = stripslashes($rec['fullname']);
        //$firstname = stripslashes($recs['firstname']);
        //$familyname = stripslashes($recs['familyname']);
        $title = stripslashes($rec['title']);
        //echo("Full name $fullname<BR>");


        if ($usetitle == 1) {
            $name = $title . " " . $fullname;
        } else {
            $name = $fullname;
        }
    }
    return $name;
}

function GetStaffNameFromUsername($db, $staff_username, $staff_table, $usetitle)
{
    $name = "";
    if ($staff_username == "") {
        return $name;
    }
//echo("Staff table $staff_table<BR>");
    $get_list_query = "select * from $staff_table where username='$staff_username'";
    try {
        $get_list_res = $db->query($get_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from database. Query: $get_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");
        return $name;
    }

    $rows = $get_list_res->fetchAll();
    //$get_list_res->closeCursor();

    foreach($rows as $rec) {
        //$initials = $recs['initials'];
        $fullname = stripslashes($rec['fullname']);
        //$firstname = stripslashes($recs['firstname']);
        //$familyname = stripslashes($recs['familyname']);
        $title = stripslashes($rec['title']);
        //echo("Full name $fullname<BR>");


        if ($usetitle == 1) {
            $name = $title . " " . $fullname;
        } else {
            $name = $fullname;
        }
    }
    return $name;
}

function GetStaffIDFromName($db, $staff_fullname, $staff_table)
{
    $staff_id = "";
    if ($staff_fullname == "") {
        return $staff_id;
    }
    $get_list_query = "select * from $staff_table where fullname='$staff_fullname'";
    try {
        $get_list_res = $db->query($get_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from database. Query: $get_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return $staff_id;
    }
    $rows = $get_list_res->fetchAll();

    foreach ($rows as $recs) {
        $staff_id = $recs['staffid'];
        //$initials = $recs['initials'];
        $fullname = stripslashes($recs['fullname']);
        //$firstname = stripslashes($recs['firstname']);
        //$familyname = stripslashes($recs['familyname']);
        //$title = stripslashes($recs['title']);
        if ($fullname == $staff_fullname) {
            //echo("$staff_fullname ($staff_id)<BR>");
            return $staff_id;
        }
    }

    //echo("$staff_fullname ($staff_id)<BR>");
    return $staff_id;
}

function GetStaffIDFromFamilyNameAndInitial($db, $staff_family_name, $staff_table)
{
    // Finds match for:
    // Sleigh
    // Sleigh A
    // Sleigh Andrew
    //
    $staff_id = "0";
    if ($staff_family_name == "") {
        return $staff_id;
    }

    // Split the family name as it may contain a training initial
    // Code needed to deal with tha
    //

    $staff_family_name_split = explode(" ", $staff_family_name);


    $get_list_query = "select * from $staff_table where familyname LIKE '%{$staff_family_name_split[0]}%'";

    if (count($staff_family_name_split) > 1) {
        $get_list_query .= " AND firstname LIKE '{$staff_family_name_split[1]}%'";
    }

    try {
        $get_list_res = $db->query($get_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from database. Query: $get_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return $staff_id;
    }
    //$num_staff_matches = $get_list_res->rowCount();

    $rows = $get_list_res->fetchAll();

    foreach ($rows as $recs) {
        $staff_id = $recs['staffid'];
        //$initials = $recs['initials'];
        //$fullname = stripslashes($recs['fullname']);
        //$firstname = stripslashes($recs['firstname']);
        //$familyname = stripslashes($recs['familyname']);
        //$title = stripslashes($recs['title']);
        return $staff_id;
        //
        //        if($familyname == $staff_family_name){
        //            //echo("$familyname ($staff_id)<BR>");
        //            return $staff_id;
        //        }
        //
    }

    //echo("$familyname ($staff_id)<BR>");
    return $staff_id;
}

function IsStaff($db, $username, $staff_names)
{

    $query = "SELECT * FROM $staff_names WHERE username='$username'";

    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        echo "An Error occured running query<BR>\n";
        echo(" Query = $query<BR \n");
        echo("Getting the error message:<BR>");
        $err_msg = $ex->getMessage();
        echo("Err msg: " . $err_msg);
        return false;
    }

    if ($results->rowCount() > 0) {
        //$rows = $results->fetchAll();
        $results->closeCursor();

        //$staff_id = $result['staffid'];
        return true;
    }

    return false;
}

function IsStudent($db, $username, $students)
{
    $query = "SELECT * FROM $students WHERE username='$username'";

    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        echo "An Error occured running query<BR>\n";
        echo(" Query = $query<BR \n");
        $err_msg = $ex->getMessage();
        echo($err_msg);
        return false;
    }

    if ($results->rowCount() > 0) {
        //$rows = $results->fetchAll();
        $results->closeCursor();
        //$staff_id = $result['staffid'];
        return true;
    }

    return false;
}

function GetSuperAdminLevel($db, $username)
{
    // Returns the admin level - 0 if no admin privilidges
    //
    $admin_level = 0;
    $query = "SELECT * FROM admin_users WHERE username='$username'";

    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        echo "An Error occured running query<BR>\n";
        echo(" Query = $query<BR \n");
        $err_msg = $ex->getMessage();
        echo($err_msg);
        return $admin_level;
        //exit();
    }

    if ($results->rowCount() > 0) {
        $rows = $results->fetchAll();

        foreach($rows as $rec) {
            $admin_level = $rec['admin_level'];
        }
        $results->closeCursor();

    }

    return $admin_level;
}

function access_check_response($db,$this_staff_username, $this_staff_full_name, $staff_names_table,&$access_response_phrase){
    $access = false;
    if (GetSuperAdminLevel($db, $this_staff_username) ){
        $access_phrase = "Hello $this_staff_full_name ($this_staff_username), you have <em>system level</em> admin access. Be very careful.";
        $access = true;
    }else{
        $access_phrase = "Unfortunately you, $this_staff_full_name ($this_staff_username), do not have admin access rights. <BR> Please return to the last page you came from.";
    }

    $access_response_phrase = $access_phrase;
    return $access;
}

function GetStudentIDFromUsername($db, $student_username, $students_table)
{
// Get the student name from SID
    $query = "select * from $students_table where username = $student_username";
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
    foreach($rows as $rec) {

        $sid = $rec['sid'];
    }
    $results->closeCursor();

    return $sid;
}

function GetStudentNameFromUsername($db, $student_username, $students_table)
{
// Get the student name from SID
    $query = "select * from $students_table where username = $student_username";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo("An Error occured accessing the database in function: $this_function <BR>\n");
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }
    //$recs = $results->fetch();
    $rows = $results->fetchAll();
    foreach($rows as $rec) {
        $name = $rec['name'];
    }
    $results->closeCursor();

    return $name;
}

function GetStudentUsernameFromSID($db, $sid, $students_table)
{
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
    //$recs = $results->fetch();
    $rows = $results->fetchAll();
    foreach($rows as $rec) {
        $username = $rec['username'];
    }
    $results->closeCursor();
    return $username;
}

function GetStudentNameFromSID($db, $sid, $students_table)
{
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
    //$recs = $results->fetch();
    //$name = $recs['name'];
    $rows = $results->fetchAll();
    foreach($rows as $rec) {
        $name = $rec['name'];
    }
    $results->closeCursor();
    return $name;
}

function DoesStaffExistByUsername($db, $staff_username, $staff_table)
{
    $staff_id = GetStaffIDFromUsername($db, $staff_username, $staff_table);
    if ($staff_id == "") {
        return false;
    } else {
        return true;
    }

}

function GetStaffIDFromUsername($db, $staff_username, $staff_table)
{
    $staff_id = "";
    if ($staff_username == "") {
        return $staff_id;
    }
    $get_list_query = "select * from $staff_table where username='$staff_username'";
    try {
        $get_list_res = $db->query($get_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from database. Query: $get_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return $staff_id;
    }

    $rows = $get_list_res->fetchAll();

    foreach ($rows as $recs) {
        $staff_id = $recs['staffid'];
        //$initials = $recs['initials'];
        //$fullname = stripslashes($recs['fullname']);
        //$firstname = stripslashes($recs['firstname']);
        //$familyname = stripslashes($recs['familyname']);
        //$title = stripslashes($recs['title']);
        $username = stripslashes($recs['username']);
        if ($username == $staff_username) {
            //echo("$staff_username, $fullname ($staff_id)<BR>");
            return $staff_id;
        }
    }
    $get_list_res->closeCursor();

    //echo("$staff_fullname ($staff_id)<BR>");
    return $staff_id;
}

function GetStaffSelectID($db, $staff_table, $NameKey, $MatchID)
{
    $staff_select_block = "\n<select name=\"$NameKey\" id=\"$NameKey\">\n";
    $staff_select_block .= "<option value=\"-1\">--- Select Staff Name ---</option>\n";
    $get_staff_list_query = "select * from $staff_table order by familyname";
    try {
        $get_staff_list_res = $db->query($get_staff_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from table. Query: $get_staff_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return $staff_select_block;
    }

    $rows = $get_staff_list_res->fetchAll();

    foreach ($rows as $recs) {
        $initials = $recs['initials'];
        $staffname = stripslashes($recs['fullname']);
        $staffid = $recs['staffid'];
        $inactive = $recs['inactive'];

        if ($inactive == 0) {
            if ($staffid == $MatchID) {
                $staff_select_block .= "<option value=\"$staffid\" selected=\"selected\">$staffname</option>\n";
            } else {
                $staff_select_block .= "<option value=\"$staffid\">$staffname</option>\n";
            }
        }
    }
    $staff_select_block .= "</select>\n";

    $get_staff_list_res->closeCursor();

    return $staff_select_block;
}

function GetStaffSelectIDWithOnChange($db, $staff_table, $NameKey, $MatchID, $OnChange)
{
    $staff_select_block = "\n<select name=\"$NameKey\" onchange=\"$OnChange\">\n";
    $staff_select_block .= "<option value=\"-1\">--- Select Staff Name ---</option>\n";
    $get_staff_list_query = "select * from $staff_table order by familyname";
    try {
        $get_staff_list_res = $db->query($get_staff_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from table. Query: $get_staff_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return $staff_select_block;
    }

    $rows = $get_staff_list_res->fetchAll();

    foreach ($rows as $recs) {
        $initials = $recs['initials'];
        $staffname = stripslashes($recs['fullname']);
        $staffid = $recs['staffid'];
        $inactive = $recs['inactive'];

        if ($inactive == 0) {
            if ($staffid == $MatchID) {
                $staff_select_block .= "<option value=\"$staffid\" selected=\"selected\">$staffname</option>\n";
            } else {
                $staff_select_block .= "<option value=\"$staffid\">$staffname</option>\n";
            }
        }
    }
    $staff_select_block .= "</select>\n";
    $get_staff_list_res->closeCursor();

    return $staff_select_block;
}

function CompareIDandFullname($db, $staff_table, $StaffID, $StaffName)
{
    $get_staff_list_query = "select * from $staff_table where staffid = $StaffID";
    try {
        $get_staff_list_res = $db->query($get_staff_list_query);
    } catch (PDOException $ex) {
        echo("Unable to create staff name list from table. Query: $get_staff_list_query <BR>\n");
        $err_msg = $ex->getMessage();
        echo($err_msg . "<BR>\n");

        return FALSE;
    }
    //$recs = $get_staff_list_res->fetch();
    //$get_staff_list_res->closeCursor();

    //$initials = $recs['initials'];
    //$staffname = stripslashes($recs['fullname']);
    //$staffid = $recs['staffid'];
    //$inactive = $recs['inactive'];
    //$fullname = $recs['fullname'];
    $rows = $get_staff_list_res->fetchAll();
    foreach($rows as $rec) {
        $fullname = $rec['fullname'];
    }
    if ($StaffName == $fullname) {
        return TRUE;
    }
    $get_staff_list_res->closeCursor();

    return FALSE;
}

function CountSupervisedProjectsByID($db, $projects_master_name_table, $staffid)
{
    $num_supervise = 0;

    $query = "select * from $projects_master_name_table where f_supervisor_id = $staffid";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");

        exit();
    }
    $num_supervise = $results->rowCount();

    $results->closeCursor();

    return $num_supervise;
}

function CountSecondMarkedProjectsByID($db, $project_details_table, $staffid)
{
    $num_second_mark = 0;
    $query = "select * from $project_details_table where f_assocstaff_id = $staffid";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");

        exit();
    }
    $num_second_mark = $results->rowCount();
    $results->closeCursor();

    return $num_second_mark;
}


/**
 *  Ã¢â‚¬Ëœ  8216  curly left single quote
 *  Ã¢â‚¬â„¢  8217  apostrophe, curly right single quote
 *  Ã¢â‚¬Å“  8220  curly left double quote
 *  Ã¢â‚¬Â  8221  curly right double quote
 *  Ã¢â‚¬â€  8212  em dash
 *  Ã¢â‚¬â€œ  8211  en dash
 *  Ã¢â‚¬Â¦  8230  ellipsis
 */
function convert_smart_quotes($string)
{
    $search = array(
        '&',
        '<',
        '>',
        '"',
        chr(212),
        chr(213),
        chr(210),
        chr(211),
        chr(209),
        chr(208),
        chr(201),
        chr(145),
        chr(146),
        chr(147),
        chr(148),
        chr(151),
        chr(150),
        chr(133)
    );

    $replace = array(
        '&amp;',
        '&lt;',
        '&gt;',
        '&quot;',
        '&#8216;',
        '&#8217;',
        '&#8220;',
        '&#8221;',
        '&#8211;',
        '&#8212;',
        '&#8230;',
        '&#8216;',
        '&#8217;',
        '&#8220;',
        '&#8221;',
        '&#8211;',
        '&#8212;',
        '&#8230;'
    );
    return str_replace($search, $replace, $string);
}

?>
