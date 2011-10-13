<?php

# Loads a couple of vars from /etc/hipbx.d/ldap.conf
# Should be in the format:
#
# LDAPHOST=domaincontroller
# LDAPPORT=389
# LDAPUSER=normaluser@domain.com
# LDAPPASS=theirpassword
# DSN="dc=domain,dc=com"
#
# I expect you to know what you're doing here.


$config = parse_ini_file("/etc/hipbx.d/ldap.conf", INI_SCANNER_RAW);
if ($config === false) {
	jerror("Unable to parse /etc/hipbx.d/ldap.conf");
}
$ldaphost = $config['LDAPHOST'];
$ldapport = $config['LDAPPORT'];
$user =     $config['LDAPUSER'];
$pass =     $config['LDAPPASS'];
$dsn =      $config['DSN'];

if ($ldaphost === "") {
	jerror('Unable to load some /etc/hipbx.d/ldap.conf variables');
}
$ldap = ldap_connect($ldaphost, $ldapport) or jerror("Can't connect to ldap host $ldaphost on port $ldapport");
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
ldap_bind($ldap, $user, $pass) or jerror("LDAP auth failed with username $user");

if (!isset($_REQUEST['uid'])) {
	jerror('No uid specified');
}
$filter = 'uid='.$_REQUEST['uid'];
$attrib = array('givenName', 'sn', 'mail', 'telephoneNumber');
$result = ldap_search($ldap, $dsn, $filter, $attrib);
$info = ldap_get_entries($ldap, $result);
if ($info['count'] === 0) {
	$json = array(
		'status' => '<i>Not Found</i>',
		'result' => 'ok',
	);
	echo json_encode($json);
	exit;
}
		
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
	"result" => "ok",
);
echo json_encode($json);

function jerror($err) {
	$json = array(
	"status" => $err,
	"result" => "error",
	);
	echo json_encode($json);
	exit;
}
?>
