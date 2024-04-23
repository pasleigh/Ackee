<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 14/07/2016
 * Time: 10:10
 */
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot find this file to include: table_functions.php<BR>");

/*
echo("<table border=0>");
foreach ($_REQUEST as $key=>$val )
{
  echo "<tr><td>".$key."</td><td>" .$val."</tr>";
}
echo("</table>");
*/

$display_block = "";

// Check the admin level - done in setup-params

$display_block .= "<h2 style='font-size: x-small; color: darkgoldenrod'>$access_phrase</h2>\n";

if ($access) {
    if (array_key_exists('pm_id', $_REQUEST) == false) {
        $_REQUEST['pm_id'] = null;
    }
    $pm_id = $_REQUEST['pm_id'];

    if (array_key_exists('op', $_POST) == false) {
        $_POST['op'] = null;
    }
    $POST_OP = $_POST['op'];

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
        $level = $recs['level'];
        $version = $recs['version'];
        $year_end_short = $year_end - 2000;

        $resit_text = "";
        if ($version == 2) {
            $resit_text = ", resit";
        }
    } else {
        $display_block .= "This year (<B>$year_begin</B>) does not seem to exist.<BR>";
        $display_block .= "Go back to the <A href=\"./admin_index.php\">admin index</A> page.<BR>&nbsp;<BR>\n";
    }


    if ($POST_OP == "delete") {
        $display_block .= "<DIV style='border-style: solid; border-width: 1px; border-color: green; padding: 5px; background-color: white' >\n";
        $display_block .= "<P>Delete the entry for year $year_begin-$year_end_short, level $level $resit_text </P><BR>\n";
        $display_block .= delete_marks_tables_for_year($db, $year_end, $level, $version, $marks_index);
        $display_block .= "</DIV>\n";
    } else {// End of POST select
        // Question whether this is wise
        $display_block .= "<DIV style='border-style: solid; border-width: 3px; border-color: red; padding: 5px; background-color: #f1c40f' >\n";
        $display_block .= "<p>Do you want to DELETE all of the data for year $year_begin-$year_end_short, level $level $resit_text ?</p>\n";
        $display_block .= "<p>Click the button if you do. Click here to go back <A href=\"./admin_index.php\">back to the admin index</A></p>\n";

        $display_block .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]?pm_id=$pm_id\">\n";


        $display_block .= "\n<input type=\"hidden\" name=\"op\" value=\"delete\">\n";
        $display_block .= "<input type='hidden' name='pm_id' value='$pm_id'>\n";

        $display_block .= "<p><input type=\"submit\" id=\"submitbutton\" name=\"submit\" value=\"YEP. Delete the data for this module\"></p>\n";

        $display_block .= "</form>\n";

        $display_block .= "</DIV>\n";


    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing : Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./css/my-bootstrap.css">
    <link rel="Shortcut Icon" type="image/ico" href="./images/favicon.ico"/>
</head>
<body>
<nav id="myNavbar" class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <A class="navbar-brand" href="./index.php">AcKee</A>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Module Charts <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./module_summary_bootstrap.php">Summary Charts</a></li>
                        <li><a href="./module_detail_bootstrap.php">Detail Charts</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Student Charts <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./student_detail_bootstrap.php">Detailed summary</a></li>
                        <li><a href="./student_weekly_bootstrap.php">Weekly Summary</a></li>


                    </ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">>
                <li><a href="./admin_index_bootstrap.php" target="_top"><span class="glyphicon glyphicon-cog"></span> Admin</a></li>
            </ul>
        </div>
    </div>
</nav>

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

</BODY>
</HTML>
