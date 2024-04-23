<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 28/12/2016
 * Time: 12:48
 */
$ackee_bookstrap_links = <<<EOF_BOOTSTRAP_LINKS
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.3/js/jquery.dataTables.min.js"></script>
    <script src="js/bowser.min.js"></script>
    <script src="./js/docCookies.js"></script>
    <link href="https://cdn.datatables.net/1.10.3/css/jquery.dataTables.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="./css/my-bootstrap.css">
    <link rel="Shortcut Icon" type="image/ico" href="./images/here.ico"/>
EOF_BOOTSTRAP_LINKS;

$ackee_nav_bar = <<<EOF_NAV
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
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Large Class charts</li>
                        <li role="separator" class="divider"></li>
                        <li><a href="./module_summary_filtered.php">Large Class Summary Charts</a></li>
                        <li><a href="./module_detail_filtered.php">Large Class Detail Charts</a></li>
                        <!--<li role="separator" class="divider"></li>
                        <li class="dropdown-header">Small Class charts</li>
                        <li role="separator" class="divider"></li>
                        <li><a href="./module_summary_filtered_small.php">Small Class Summary Charts</a></li>
                        <li><a href="./module_detail_filtered_small.php">Small Class Detail Charts</a></li>-->
                    </ul>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Student Charts <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./student_detail_bootstrap.php">Detailed summary</a></li>
                        <li><a href="./student_weekly_bootstrap.php">Weekly Summary</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Large Class charts</li>
                        <li role="separator" class="divider"></li>
                        <li><a href="./student_detail_filtered.php">Large Class Detailed summary</a></li>
                        <li><a href="./student_weekly_filtered.php">Large Class Weekly Summary</a></li>

                    </ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">>
                <li><a href="./admin_index_bootstrap.php" target="_top"><span class="glyphicon glyphicon-cog"></span> Admin</a></li>
            </ul>
        </div>
        <!-- dropdown -->


    </div>
</nav>
EOF_NAV;

?>