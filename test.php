<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if(!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }
      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}
echo("Hi Im in php");
$test = "test words";

echo($test);
try{
  $test_json = json_encode($test);
}catch (Exception $e){
  echo("Exception" . $e->getMessage());
}
echo("<BR>In json format: $test_json<BR>");

//$mod_summary_data[] = array("cive1456", "10", "78", "78", "1");
//$mod_summary_data[] = array("cive1468", "20", "88", "65", "2");
$module_irq_data[] =array(0, 10, 20, 30, 90);
$module_irq_data[] =array(0, 10, 40, 30, 90);
$module_means[] = 40;
$module_means[] = 50;
$module_names[] = "ist";
$module_names[] = "2nd";
$plot_title = "Test title";
echo("$plot_title<BR>\n");
var_dump($module_means);
var_dump($module_names);
var_dump($module_irq_data);
?>
<P>Hello php</P>

