<?php

# Checks for, and returns, an users with a callgroup or pickupgroup matching
# the number supplied. Calling freepbx for this may be a bit expensive in CPU
# time, but I'm lazy.
$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
	include_once('/etc/asterisk/freepbx.conf');
}
# Ensure that we have a working json library.
require_once 'includes/provisioner/samples/json.php';

# For debugging, set this to a valid, used, pickup group.
#$_REQUEST['callgroup']=60;

if (!isset($_REQUEST['callgroup'])) {
        echo json_encode(array('result' => 'fail', status => 'Script Error', 'extens' => ''));
	exit;
}

$callgroup = $_REQUEST['callgroup'];

$sql = "SELECT distinct(s.id), u.name from sip s, users u where keyword='callgroup' and data='$callgroup' and s.id=u.extension";
$res = $db->query($sql);
if($res->numRows() == 0) {
	$json = array('status' => 'No members', 'result' => 'ok', 'extens' => '');
	echo json_encode($json);
	exit;
}

$json['result']='ok';
$json['status']=$res->numRows().' members found';
while ($line = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
	$json['extens'] = $json['extens'].$line['id'].' - '.$line['name']."<br />\n";
}
echo json_encode($json);


