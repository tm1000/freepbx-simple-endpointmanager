<?php
$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}

switch($_REQUEST['type']) {
    case 'validext':
        $sql = 'SELECT * FROM devices WHERE id = '. $_REQUEST['ext'];
        $res = $db->query($sql);
        if($res->numRows() > 0) {
            $json = array('status' => false);
        } else {
            $json = array('status' => true);
        }
        break;
    case 'validmac':
            $sql = "SELECT * FROM simple_endpointman_mac_list WHERE mac = '". $_REQUEST['mac']."'";
            $res = $db->query($sql);
            if($res->numRows() > 0) {
                $json = array('status' => false);
            } else {
                $json = array('status' => true);
            }
        break;
    case 'checkuser':
	# Here would be clever stuff to do.. things.
	if ($_REQUEST['uid'] === 'valid') {
		$json = array('email' => 'fake@fake.com', 'ext' => '333', 'voicemail' => 'yes', 'username' => 'Full Name');
	} else {
		$json = array('email' => '', 'ext' => '', 'voicemail' => 'no');
	}
	break;
    default:
        $json = array('status' => false);
        break;
}
echo json_encode($json);
