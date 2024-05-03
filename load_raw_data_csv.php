<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./connect_pdo.php")) OR die("Cannot connect to the database<BR>");

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
        $table_name_raw = GetRawDataTableName($school_short_name_raw,$year_raw,$issue_raw,$version_raw);

        if($version_raw == 1 ){
            // Create table version 1
        }else if($version_raw == 2 ){
            // Create table version 2
        }else{
            // abort and issue error
        }
        $query = "LOAD DATA LOCAL INFILE '.$filename_raw.'
        INTO TABLE '.$table_name_raw.'
        FIELDS TERMINATED by \',\'
        LINES TERMINATED BY \'\n\'";

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
        // And to params if needed
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
                <label for="mysql" class="control-label col-xs-2">Mysql Server address (or)<br>Host name</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="mysql" id="mysql" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label for="username" class="control-label col-xs-2">Username</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="username" id="username" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="control-label col-xs-2">Password</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="password" id="password" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label for="db" class="control-label col-xs-2">Database name</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="db" id="db" placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label for="table" class="control-label col-xs-2">table name</label>
                <div class="col-xs-3">
                    <input type="name" class="form-control" name="table" id="table">
                </div>
            </div>
            <div class="form-group">
                <label for="csvfile" class="control-label col-xs-2">Name of the file</label>
                <div class="col-xs-3">
                    <input type="name" class="form-control" name="csv" id="csv">
                </div>
                eg. MYDATA.csv
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

</body>


