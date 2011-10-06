<?php
$blank = FALSE;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}
        
include('includes/webprov.php');

$prov = new webprov();

$location = isset($_REQUEST['location']) ? $_REQUEST['location'] : '';
$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if($type == 'add') {
    $blank = TRUE;
    $mac  = isset($_REQUEST['mac']) ? $_REQUEST['mac'] : '';
    $device  = isset($_REQUEST['device']) ? $_REQUEST['device'] : '';
    $ext  = isset($_REQUEST['ext']) ? $_REQUEST['ext'] : '';
    $name  = isset($_REQUEST['displayname']) ? $_REQUEST['displayname'] : '';

    dbug($device);
    
    if($prov->add_device($mac,$device,$ext,$name)) {
        $array = array('success' => 'true','ext' => 'bbb', 'mac' => 'hhh');
    } else {
        $array = array('success' => 'false');
    }
    echo json_encode($array);
}

if(!$blank) {
    $prov->tpl->draw( 'header' );
    switch($location) {
        case 'add':
            $prov->tpl->assign( 'devices', $prov->get_devices('SPA504G') );
            $prov->tpl->draw( 'add' );
            break;
        case 'swap':
            $prov->tpl->draw( 'swap' );
            break;
        default:
            $prov->tpl->draw( 'index' );
            break;
    }
    $prov->tpl->draw( 'footer' );
}