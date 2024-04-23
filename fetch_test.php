<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

(@include_once("./json_encode_for_php51.php")) OR die("Cannot find this file to include: json_encode_for_php51.php<BR>");

$mod_summary_data[] = array("cive1456", "10", "78", "78", "1");
$mod_summary_data[] = array("cive1468", "20", "88", "65", "2");
$module_irq_data[] =array(0, 10, 20, 30, 90);
$module_irq_data[] =array(0, 10, 40, 30, 90);
$module_means[] = 40;
$module_means[] = 50;
$module_names[] = "ist";
$module_names[] = "2nd";
$plot_title = "Test title";
$graph_data = array('module_iqrs' => $module_irq_data, 'module_summary_data' => $mod_summary_data, 'module_means' => $module_means, 'module_names' => $module_names, 'plot_title' => $plot_title);

//var_dump($graph_data);
//echo("<BR>And now the json:<BR>");
$in_json = json_encode($graph_data);
echo($in_json);


?>



