<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");
(@include_once("./table_functions.php")) OR die("Cannot find this file to include: table_functions.php<BR>");

(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");
/*
echo("<table border=0>");
foreach ($_REQUEST as $key=>$val )
{
  echo "<tr><td>".$key."</td><td>" .$val."</tr>";
}
echo("</table>");
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$school_name_raw = "";
if (array_key_exists('school_name', $_POST)) {
    $school_name_raw = $_POST['school_name'];
}
$school_short_name_raw = "";
if (array_key_exists('school_short_name', $_POST)) {
    $school_short_name_raw = $_POST['school_short_name'];
}
$year_raw = "";
if (array_key_exists('year', $_POST)) {
    $year_raw = $_POST['year'];
}
$issue_raw = "";
if (array_key_exists('issue', $_POST)) {
    $issue_raw = $_POST['issue'];
}
$version_raw = "";
if (array_key_exists('version', $_POST)) {
    $version_raw = $_POST['version'];
}
$filename_raw = "";
if (array_key_exists('filename', $_POST)) {
    $filename_raw = $_POST['filename'];
}

$display_block = "";
if (array_key_exists('op', $_POST)) {
    if($_POST['op']='read_raw_data'){
        if(($school_short_name_raw != "")&&($year_raw!="")&&($issue_raw!="")&&($version_raw!="")) {
            $table_name_raw = GetRawDataTableName($school_short_name_raw, $year_raw, $issue_raw, $version_raw);
            create_raw_data_table_version($db, $table_name_raw, $version_raw);

            if ($version_raw == 1) {
                // Create table version 1
            } else if ($version_raw == 2) {
                // Create table version 2
            } else {
                // abort and issue error
            }
            $query = 'LOAD DATA LOCAL INFILE \''.$filename_raw.'\'
                        INTO TABLE '.$table_name_raw.'
                        FIELDS TERMINATED BY \',\'
                        LINES TERMINATED BY \'\n\'';

            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }

            // add a primary key
            $query = "ALTER TABLE $table_name_raw ADD COLUMN `id` int(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }

            // delete the first row as it is a header line from the csv file
            $query = "DELETE FROM $table_name_raw WHERE id=1";
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }

            // Add entry to definition
            $query = "INSERT INTO $definition_table_name 
                        (school_short_name, school_title, default_module_prefix, header_image_name, running_title, running_subtitle, year, raw_data_issue, raw_data_version)
                        VALUES
                            ('$school_short_name_raw', '$school_name_raw', 
                             'CIVE', 'leeds_eng_header.png', 'AcKee', 'View Attendance Records', 
                             '$year_raw', '$issue_raw', '$version_raw')";
            //echo($query);
            //exit();
            try {
                $results = $db->query($query);
            } catch (PDOException $ex) {
                $this_function = __FUNCTION__;
                echo "An Error occured accessing the database in function: $this_function <BR>\n";
                echo(" Query = $query<BR> \n");
                echo(" Err message: " . $ex->getMessage() . "<BR>\n");
                exit();
            }
            // And to params if needed
        }
    }
}
if(isset($_POST['username'])&&isset($_POST['mysql'])&&isset($_POST['db'])&&isset($_POST['username']))
{
    $sqlname=$_POST['mysql'];
    $username=$_POST['username'];
    $table=$_POST['table'];
    if(isset($_POST['password']))
    {
        $password=$_POST['password'];
    }
    else
    {
        $password= '';
    }
    $db=$_POST['db'];
    $file=$_POST['csv'];
    $cons= mysqli_connect("$sqlname", "$username","$password","$db") or die(mysql_error());

    $result1=mysqli_query($cons,"select count(*) count from $table");
    $r1=mysqli_fetch_array($result1);
    $count1=(int)$r1['count'];
//If the fields in CSV are not seperated by comma(,)  replace comma(,) in the below query with that  delimiting character
//If each tuple in CSV are not seperated by new line.  replace \n in the below query  the delimiting character which seperates two tuples in csv
// for more information about the query http://dev.mysql.com/doc/refman/5.1/en/load-data.html
    mysqli_query($cons, '
    LOAD DATA LOCAL INFILE "'.$file.'"
        INTO TABLE '.$table.'
        FIELDS TERMINATED by \',\'
        LINES TERMINATED BY \'\n\'
')or die(mysql_error());

    $result2=mysqli_query($cons,"select count(*) count from $table");
    $r2=mysqli_fetch_array($result2);
    $count2=(int)$r2['count'];

    $count=$count2-$count1;
    if($count>0)
        echo "Success";
    echo "<b> total $count records have been added to the table $table </b> ";


}else{
    echo "Mysql Server address/Host name ,Username , Database name ,Table name , File name are the Mandatory Fields";
}

// Check the admin level - done in setup-params
$display_block .= "<h3 style='color: darkgoldenrod'>$access_phrase</h3>\n";

if ($access) {

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AcKee: Load Raw Attendance Data</title>
    <?php echo($ackee_bookstrap_links)?>

    <style type="text/css">
        .jumbotron{
            margin-top: 10px;
            padding-top: 10px;
            background-color: goldenrod;
            border-width: 1px;
        }
    </style>
</head>
<body>

<?php echo($ackee_nav_bar)?>
<div class="container">

    <div id="content">
        <?php echo $display_block; ?>
        <h1>Load Raw Attendance Data from csv</h1>
        <p> This enables the import of very large CSV files to MYSQL database in a few seconds<BR>Further processing is required</p>

        <h3>Important</h3>
        <ol>
            <li>The CVS file <strong>MUST</strong> be in the same folder as this .php file</li>
            <li>The raw data table will be made up of:</li>
            <ul>
                <li>school short name</li>
                <li>year of the data</li>
                <li>issue number of the data (1, 2, 3 etc)</li>
                <li>data format version (1 or 2)</li>
            </ul>
        </ol>

        </br>
        <form class="form-horizontal" action="<?php  echo($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="form-group">
                <label for="school_name" class="control-label col-xs-2">School long name <BR>(e.g. Civil Engineering)</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="school_name" id="school_name" value="Civil Engineering">
                </div>
            </div>
            <div class="form-group">
                <label for="school_short_name" class="control-label col-xs-2">School short name <BR>(e.g. Civil)</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control raw_data" name="school_short_name" id="school_short_name" value="Civil">
                </div>
            </div>
            <div class="form-group">
                <label for="year" class="control-label col-xs-2">Year start <BR>(year of the start of the academic session)</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control raw_data" name="year" id="year" value="2016">
                </div>
            </div>
            <div class="form-group">
                <label for="issue" class="control-label col-xs-2">Data issue number <BR>(e.g. normally 1, but 2, 3, etc. for other versions for the same school)
                </label>
                <div class="col-xs-1">
                    <input type="number" class="form-control raw_data" name="issue" id="issue" min="1" value="1" placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label for="version" class="control-label col-xs-2">Data format version <BR>(1 or 2)</label>
                <div class="col-xs-1">
                    <input type="number" class="form-control raw_data" name="version" id="version" min="1" max="2" value="2">
                </div>

            </div>
            <input type="checkbox" id="toggle_header_images" > Show images of the header for each format version  of the data
            <div id="header_images" style="display: none; border: 1px solid black;padding: 5px">
                <h4>This is the header of the csv file for version 1</h4>
                <img src="./images/v1_data_format.png"/><BR>
                <h4>This is the header of the csv file for version 2</h4>
                <img src="./images/v2_data_format.png"/>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label for="raw_data_tablename" class="control-label col-xs-2">Database table name</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="raw_data_tablename" id="raw_data_tablename" value="attend_raw_..." disabled>
                </div>

            </div>
            <div class="form-group" style="margin-top: 20px;">
                <label for="filename" class="control-label col-xs-2">The CSV file to import</label>
                <div class="col-xs-6">
                    <input type="file" accept="text/csv" class="form-control" name="filename" id="filename">
                </div>
            </div>
            <div class="form-group">
                <label for="login" class="control-label col-xs-2"></label>
                <div class="col-xs-3">
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
            <input type="hidden" name="op" id="op" value="read_raw_data">
        </form>
    </div>

    </div><!-- /content -->

</div>
<footer class="footer">
    <div class="container">
        <p class="text-muted">If you have any questions, contact Andrew Sleigh: P.A.Sleigh@leeds.ac.uk</p>
    </div>
</footer>
<script>
        $(document).ready(function() {
            $("#toggle_header_images").click(function() {
                $("#header_images").toggle()
            });

            $('.raw_data').on("keyup change", function () {
                // Do magical things
                //let school_name = $("#school_name").value()
                make_and_load_database_tablename();
            });

            function make_and_load_database_tablename(){
                let school_short_name = $("#school_short_name").val()
                let year_begin = $("#year").val()
                let issue = $("#issue").val()
                let version = $("#version").val()
                let database_tablename = `attend_raw_${school_short_name}_${year_begin}_i${issue}_v${version}`
                $("#raw_data_tablename").val(database_tablename)
            }
            make_and_load_database_tablename();
        });
</script>
</body>


