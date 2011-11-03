<?php
$blank = FALSE;
require_once 'includes/provisioner/samples/json.php';

$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
    include_once('/etc/asterisk/freepbx.conf');
}
        
include('includes/webprov.php');

$prov = new webprov();

$location = isset($_REQUEST['location']) ? $_REQUEST['location'] : '';
$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if(!$blank) {
    $prov->tpl->draw( 'header' );
    switch($location) {
        case 'add':
            $start_mac = isset($_REQUEST['mac']) ? $_REQUEST['mac'] : '';
            $prov->tpl->assign( 'start_mac', $start_mac );
            $prov->tpl->assign( 'devices', $prov->get_devices('SPA502G') );
            $prov->tpl->draw( 'add' );
            break;
        case 'swap':
            $prov->tpl->assign( 'devices', $prov->get_managed_devices() );
            $prov->tpl->draw( 'swap' );
            break;
	case 'del':
            $prov->tpl->assign( 'devices', $prov->get_managed_devices() );
            $prov->tpl->draw( 'del' );
            break;
	case 'manage':
            $prov->tpl->assign( 'devices', $prov->get_managed_devices() );
            $prov->tpl->draw( 'manage' );
            break;
        case 'manage_phone':
            include 'includes/edit_phone.php';
            break;
        default:
            $prov->tpl->assign( 'address', 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"].'p.php/' );
            $prov->tpl->draw( 'index' );
            break;
    }
    $prov->tpl->draw( 'footer' );
}
