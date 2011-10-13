<?php

$ldaphost = "localhost";
$ldapport = 389;
$user = 'username@domain.local';
$pass = 'password';
$dsn = "dc=domain,dc=local";

$ldap = ldap_connect($ldaphost, $ldapport) or die("Can't connect to ldap");
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
ldap_bind($ldap, $user, $pass) or die ("LDAP bind failed...");

$filter = $_REQUEST['uid'];
$attrib = array('givenName', 'sn', 'mail', 'telephoneNumber');
$result = ldap_search($ldap, $dsn, $filter, $attrib);
$info = ldap_get_entries($ldap, $result);
# Unconfuse telephone number.
$pn = $info[0]['telephonenumber'][0];
if (strlen($pn) > 3) {
	$ext = substr($pn, -3);
} else {
	$ext = $pn;
}
$json = array( 
	"name" => $info[0]['givenname'][0]." ".$info[0]['sn'][0],
	"email" => $info[0]['mail'][0],
	"ext" => $ext,
	"phone" => $pn,
	"pin" => sprintf('%04d', rand(0,9999)),
);
echo json_encode($json);
?>
