<?php
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 21/10/2016
 * Time: 16:45
 */
if (array_key_exists('op', $_REQUEST) == false) {
    $_REQUEST['op'] = null;
}
$OP = $_REQUEST['op'];
if (array_key_exists('pwd', $_REQUEST) == false) {
    $_REQUEST['pwd'] = null;
}
if (array_key_exists('username', $_REQUEST) == false) {
    $_REQUEST['username'] = null;
}

$display_block = "";

if($OP == 'submit_pwd'){
    $pwd = $_REQUEST['pwd'];
    $username = $_REQUEST['pwd'];
    $display_block .= "<div>\n";
    $display_block .= "username: $username <BR>\n";
    if(!is_null($OP)){
        $display_block .= "password recieved.<br>\n";
    }

    // ldap verification
    $ldaphost = 'ldapServer';
    $ldapport = 389;

    $ds = ldap_connect($ldaphost, $ldapport);


    //$ds=ldap_connect("localhost");  // assuming the LDAP server is on this host

    if ($ds) {

        // bind
        if (ldap_bind($ds)) {

            // prepare data
            $dn = "cn=Matti Meikku, ou=My Unit, o=My Company, c=FI";
            $value = "secretpassword";
            $attr = "password";

            // compare value
            $r=ldap_compare($ds, $dn, $attr, $value);

            if ($r === -1) {
                echo "Error: " . ldap_error($ds);
            } elseif ($r === true) {
                echo "Password correct.";
            } elseif ($r === false) {
                echo "Wrong guess! Password incorrect.";
            }

        } else {
            echo "Unable to bind to LDAP server.";
        }

        ldap_close($ds);

    } else {
        echo "Unable to connect to LDAP server.";
    }
    $display_block .= "</div>\n";

}else{
    $display_block .= "<form method='post' action='$_SERVER[PHP_SELF]'>";
    $display_block .= "Enter your username <input type='text' name='pwd' size=10 maxlength=30><BR>\n";
    $display_block .= "Enter your password <input type='password' name='username' size=10 maxlength=30>\n";
    $display_block .= "\n<input type='hidden' name='op' value='submit_pwd'>";

    $display_block .= "<p><input type='submit' id='submitbutton_pwd' name='submit' value='Submit password'></p>";

    $display_block .= "</form>";
}
?>
<HTML>
<HEAD>
</HEAD>
<BODY>
<div>

    <div id="content">
        <?php echo $display_block; ?>
    </div><!-- /content -->


</div><!-- /wrapper -->
</BODY>
</HTML>

