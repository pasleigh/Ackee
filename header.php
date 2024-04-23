<?PHP
$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
?>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Testing</title> 

  <link rel="Shortcut Icon" type="image/ico" href="<?php echo("$protocol://$BASE_URL_VALUE") ?>/images/favicon.ico"/>
    
  <link href="<?php echo("$protocol://$BASE_URL_VALUE") ?>/css/pagedesc.css" type="text/css" rel="stylesheet" />

  <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
  <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.3/js/jquery.dataTables.min.js"></script>
  <link href="https://cdn.datatables.net/1.10.3/css/jquery.dataTables.css" type="text/css" rel="stylesheet" />
</head>
  
