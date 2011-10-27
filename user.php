<?php
require_once 'includes/provisioner/samples/json.php';

$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}
        
include('includes/webprov.php');
?>
<link rel="stylesheet" href="/recordings/theme/main.css" type="text/css" />
<?php

$prov = new webprov();

$ext = isset($_REQUEST['ext']) ? $_REQUEST['ext'] : '';
$md5web = isset($_REQUEST['md5']) ? $_REQUEST['md5'] : '';

$sql = "SELECT data FROM sip WHERE id = '".$ext."' AND keyword = 'secret'";
$md5secret = md5($prov->db->getOne($sql));

if ($md5secret == $md5web) {
    
    $sql = "SELECT simple_endpointman_mac_list.mac FROM simple_endpointman_mac_list, simple_endpointman_line_list WHERE simple_endpointman_mac_list.id = simple_endpointman_line_list.mac_id AND simple_endpointman_line_list.ext = ".$ext;
    $mac = $prov->db->getOne($sql);
    
    $_REQUEST['mac'] = $mac;
    require_once 'includes/edit_phone.php';
} else {
    die('Unauthorized');
}
