<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 09/07/2016
 * Time: 15:47
 */

//echo("setup_titles: rel_dir = $rel_dir<BR>\n");

$DB_ADMIN_USER_VAR = DB_ADMIN_USER;

if (strlen($DB_ADMIN_USER_VAR) > 1) {
    $first_DB_ADMIN_USERS_CHARS = substr($DB_ADMIN_USER_VAR, 0, 2);
} else {
    $first_DB_ADMIN_USERS_CHARS = "";
}

if ((strlen($DB_ADMIN_USER_VAR) < 2) || ($first_DB_ADMIN_USERS_CHARS == "{{")) {
    $hostname = "localhost";        /* This is the hostname on which your MySQL is running */
    //$dbName = "civ_meditadb1";
    $dbName = "civ_projectdb2";
    //$dbName = "civ_meditadb1_empty";
    $dbusername = "civ_project";
    $password = "EngCivilLeeds";
} else {
    define('__BASE_DIR__', getenv('BASE_DIR'));
    $hostname = DB_SERVER;
    $dbName = DB_NAME;
    $dbusername = DB_ADMIN_USER;
    $password = DB_ADMIN_PASS;
}

define("__ADMIN_DIR__", __BASE_DIR__ . "/staff/admin2");
//echo("Defined __ADMIN_DIR__ Value: " . __ADMIN_DIR__ ."<BR>\n");
setcookie('BASE_DIR', __BASE_DIR__);
setcookie('ADMIN_DIR', __ADMIN_DIR__);

$setup_titles = "setup_titles.php";

date_default_timezone_set('Europe/London');

$sub_SERVER = substr($_SERVER['HTTP_HOST'], 0, 5);
//echo("subServer: $sub_SERVER<BR>\n");
if ($sub_SERVER == "local") {
    $base = $_SERVER['HTTP_HOST'] . dirname(substr($_SERVER['SCRIPT_FILENAME'], strlen($_SERVER['DOCUMENT_ROOT'])) . '/');
    $base = dirname($base);
    //define('__BASE_URL__',$base);
    $doc_root = $_SERVER['DOCUMENT_ROOT'];
    $htdocs_pos = strpos(__BASE_DIR__, "htdocs");
    $base = substr(__BASE_DIR__, $htdocs_pos + 6);
    $base = $_SERVER['HTTP_HOST'] . $base;

    //define('__BASE_URL__',$base);
    define('__BASE_URL__', getenv('BASE_URL'));
    $base = __BASE_URL__;

    //if (array_key_exists('BASE_URL', $_SESSION) == false) {
    //    $_SESSION['BASE_URL'] = $base;
    //    $_SESSION['BASE_URL_SET'] = 1;
    //}

    setcookie('BASE_URL', $base);

    /*
    $query = "SET GLOBAL general_log = 'ON'";
    try {
        $results = $db->query($query);
    } catch (PDOException $ex) {
        $this_function = __FUNCTION__;
        echo "An Error occured accessing the database in function: $this_function <BR>\n";
        echo(" Query = $query<BR> \n");
        echo(" Err message: " . $ex->getMessage() . "<BR>\n");
        exit();
    }
    */

} else {
    define(__BASE_URL__, BASE_URL);
}

$BASE_URL_VALUE = __BASE_URL__;

if (empty($db)) {
    require('./connect_pdo.php');
}

// Does the definition table exist?
$table_name = "definition";
try {
    // this will raise an exception if the table does not exist
    $query = "DESCRIBE $table_name";
    $db->query($query);

    $query = "SELECT * from $table_name LIMIT 1"; // Only want the first (there should only be one!)
    $setup = $db->prepare($query);
    $setup->execute();
    $rows = $setup->fetchAll();

    foreach($rows as $result) {
        $id = $result['id'];
        $school = $result['school_short_name'];
        $header_image = $result['header_image_name'];
        $school_title = $result['school_title'];
        $school_module_prefix = $result['default_module_prefix'];
        $running_title = $result['running_title'];
        $running_subtitle = $result['running_subtitle'];
        //echo("School: $school<BR>\n");
        //echo("Header: $header_image<BR>\n");
        //echo("School title: $school_title<BR>\n");
        //echo("School module prefix: $school_module_prefix<BR>\n");
        //echo("Running title: $running_title<BR>\n");
    }
    $setup->closeCursor();

} catch (PDOException $ex) {
    //echo "$table_name doesn't exist in database $dbName\n";
    //
    if (empty($first_use)) {
        echo("<DIV>This appears to be the first time this site has been used. <BR>Go here to setup the database: <a href='./admin_first_use.php'>First use setup</a>");
    }
}
