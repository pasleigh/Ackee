<?php
(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
<meta charset="UTF-8">
<title>AcKee Attendance Record Viewing</title>
<!-- Minified Cookie Consent served from our CDN -->
<script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js"></script>
<?php echo($ackee_bookstrap_links)?>
</head>

<body>
<?php echo($ackee_nav_bar)?>

<div class="container">
    <div class="jumbotron other-color">
        <h1><span class="ackee">AcKee</span> : Attendance Record Viewing</h1>
        <p>The charts and data presented on these pages are designed to let you see the overall attendance of students at modules.</p>
        <p>A box-and-whisker plot is used to give a quick visualisation of the mark distribution. Individual student's attendance record can be overlaid to see their position relative to the whole class. </p>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <h2>Module charts</h2>
            <p>View a comparison of modules attendance distributions by a selecting a set of module. Individual modules can also be chose to show the attendance by date.</p>
            <p><a href="./module_summary_bootstrap.php" target="_top" class="btn btn-success">Module overview charts &raquo;</a></p>
        </div>
        <div class="col-sm-6">
            <h2>Individual student charts</h2>
            <p>This page allow you to see charts for individual students. These are the charts that you might want to examine and discuss together with your tutees.</p>
            <p><a href="./student_bootstrap.php" target="_top" class="btn btn-success">Student attendance charts &raquo;</a></p>
        </div>

    </div>
</div>
    <footer class="footer">
        <div class="container">
            <p class="text-muted">If you have any questions, contact Andrew Sleigh: P.A.Sleigh@leeds.ac.uk</p>
        </div>
    </footer>

</body>

<div id="myWarningModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 id="myWarningModalHeaderText" class="modal-title">Warning</h4>
            </div>
            <div class="modal-body">
                <p id="myWarningModalText">Warning Notification</p>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!--<button type="button" class="btn btn-primary">Save changes</button>-->
            </div>
        </div>
    </div>
</div>
<script>
//if (bowser.msie || bowser.chrome || bowser.firefox) {
if (bowser.msie) {
    var msg = 'The graphs might or might not work in Microsoft Internet Explorer ....<BR>';
    msg += 'You\'re much more likely to get a better experience using ANY other browser. ';
    msg += '<P>Chrome, Firefox, Edge, Opera  etc. ... are good choices. <BR><BR>Even my Android mobile works better than IE</P>';
    //alert(msg);
    $('#myWarningModalText').html(msg);
    $('#myWarningModal').modal('show');

    var ver = parseFloat(bowser.version);
    if (ver < 11) {
        msg = 'Version ' + ver + ' is way too low. <BR>';
        msg += 'You need to be using at least version 11 of Internet Explorer to get even a reasonable experience.<BR><BR>';
        msg += 'Even then the drawing will not be very good.';
        alert(msg);
        $('#myWarningModalText').html(msg);
        $('#myWarningModal').modal('show');
    }
}
</script>
<script type="text/javascript">
    window.cookieconsent_options = {"message":"Be aware that this website uses cookies.","dismiss":"Got it!","learnMore":"More info","link":null,"theme":"light-bottom"};
</script>

</html>