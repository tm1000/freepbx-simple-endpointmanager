<?php
$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}

include('includes/webprov.php');
$prov = new webprov();

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
    case 'manage':
        $sql = "UPDATE simple_endpointman_mac_list SET model = '".$_REQUEST['model']."' WHERE simple_endpointman_mac_list.id = ". $_REQUEST['id'];
        $db->query($sql);
        
        $_REQUEST['enable_sidecar1'] = ($_REQUEST['enable_sidecar1'] == "true") ? true : false;
        $_REQUEST['enable_sidecar2'] = ($_REQUEST['enable_sidecar2'] == "true") ? true : false;
        
        $prov->set_data($_REQUEST['mac'], 'enable_sidecar1', $_REQUEST['enable_sidecar1'], 'settings', 'mac' );
        $prov->set_data($_REQUEST['mac'], 'enable_sidecar2', $_REQUEST['enable_sidecar2'], 'settings', 'mac' );
        
        $json = array('success' => true, 'model' => $_REQUEST['model'], 'stuff' => $_REQUEST['mac']);
        break;
    case 'delete':
        $prov->remove_device($_POST['mac']);
        $json = array('success' => true);
        break;
    case 'add':
        $blank = TRUE;
        $mac = isset($_REQUEST['mac']) ? $_REQUEST['mac'] : '';
        $device = isset($_REQUEST['device']) ? $_REQUEST['device'] : '';
        $ext = isset($_REQUEST['ext']) ? $_REQUEST['ext'] : '';
        $name = isset($_REQUEST['displayname']) ? $_REQUEST['displayname'] : $mac;
        $vm = isset($_REQUEST['voicemail']) ? $_REQUEST['voicemail'] : 'no';
        $vmpin = isset($_REQUEST['vmpin']) ? $_REQUEST['vmpin'] : '0000';
        $email = isset($_REQUEST['emailaddr']) ? $_REQUEST['emailaddr'] : '';
        $callgroup = isset($_REQUEST['callgroup']) ? $_REQUEST['callgroup'] : '';
        $network = isset($_REQUEST['network']) ? $_REQUEST['network'] : '';
        
        $prov_vars = array("enable_sidecar1" => false, "enable_sidecar2" => false, "network" => $network);

        if ($prov->add_device($mac, $device, $ext, $name, $vm, $vmpin, $email, $callgroup, $prov_vars)) {
            $json = array('success' => 'true', 'ext' => $name, 'mac' => $mac);
        } else {
            $json = array('success' => 'false');
        }
        break;
    case 'swap':
        $blank = TRUE;
        $newmac = isset($_REQUEST['newmac']) ? $_REQUEST['newmac'] : '';
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

        if (!empty($id) && !empty($newmac)) {

            $sql = "UPDATE simple_endpointman_mac_list SET mac = '" . $newmac . "' WHERE id = " . $id;
            $prov->db->query($sql);

            //re-boot

            $json = array('success' => true, 'newmac' => $newmac);
        } else {
            $json = array('success' => false, 'message' => 'ID or Mac not set');
        }
        break;
    case 'network':
	$provis_conf = @parse_ini_file('/etc/hipbx.d/provis.conf', false, INI_SCANNER_RAW);
	$nets=array();
	if (!isset($provis_conf['NETWORK'])) {
	  $json = array('result' => 'ok', 'text' => 'DHCP Provisioning Not Configured');
	  break;
	}
	if (!is_numeric($_REQUEST['ext'])) {
	  $json = array('result' => 'ok', 'text' => 'Extension supplied is not a number?');
	  break;
	}
	# If we don't find it, we want a reasonable error.
	$json = array('result' => 'ok', 'text' => "Odd things happening. Can't find requested network.");

	foreach($provis_conf['NETWORK'] as $name =>  $net) {
	   if ($name == $_REQUEST['net']) {
	      # Sanity check the networks. It shouldn't have a length.
	      if (preg_match('/\//', $net)) {
	  	$json = array('result' => 'ok', 'text' => "Network $name incorrectly configured (has a /length) in /etc/hipbx.d/provis.conf");
		break;
	      }
	      if (!preg_match('/(\d+)\.(\d+)\.(\d+).(\d+)/', $net, $matches)) {
	  	$json = array('result' => 'ok', 'text' => "Unable to parse $net from /etc/hipbx.d/provis.conf - should be like 10.4.0.0");
		break;
	      }
	      # OK, Finally. Everything seems ok. Lets generate an IP address.
	      preg_match('/(\d)(\d)(\d)/', $_REQUEST['ext'], $extsplit);
	      $json = array('result' => 'ok', 'text' => "IP Address assigned: $matches[1].$matches[2].10$extsplit[1].$extsplit[2]$extsplit[3]");
	      break;
	   }
	}
	break;
    default:
        $json = array('status' => false);
        break;
}
echo json_encode($json);
