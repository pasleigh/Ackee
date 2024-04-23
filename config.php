<?PHP
define("DB_SERVER", "localhost");
define("DB_ADMIN_USER", '{{mysql.username_admin}}');
define("DB_ADMIN_PASS", '{{mysql.password_admin}}');
define("DB_RO_USER", '{{mysql.username_user}}');
define("DB_RO_PASS", '{{mysql.password_user}}');
define("DB_NAME", '{{mysql.database}}');
//define("BASE_DIR", '{{values.baseDir}}');
//define("BASE_URL", '{{values.baseURL}}');

define("__BASE_DIR__", getenv('BASE_URL'));
define("BASE_URL", getenv('BASE_DIR'));

$BASE_URL = getenv('BASE_URL');
$BASE_URL_VALUE = $BASE_URL;
$BASE_DIR = getenv('BASE_DIR');
$BASE_DIR_VALUE = $BASE_DIR;
?>
  
