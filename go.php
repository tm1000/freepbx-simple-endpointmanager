<?php

require_once 'includes/provisioner/samples/json.php';

$options = array();
$lines = array();

foreach($_REQUEST as $key => $value) {
    if((preg_match('/(.*)\|(.*)\|(.*)/', $key, $matches)) OR (preg_match('/(.*)\|(.*)/', $key, $matches))) {
        switch($matches[1]) {
            case 'loop':
                if(preg_match('/(.*)_(.*)_(.*)/', $matches[2], $matches2)) {
                    $options['data'][$matches2[1]][$matches2[2]][$matches2[3]] = $value;
                } else {
                    die('Invalid Loop');
                }
                break;
            case 'option':
                $options['data'][$matches[2]] = $value;
                break;
            case 'admin':
                $options['admin'][$matches[2].'|'.$matches[3]] = TRUE;
                break;
            case 'lineloop':
                if(preg_match('/^(.*)_(\d)_(.*)$/', $matches[2], $matches2)) {
                    $lines[$matches2[2]][$matches2[3]] = $value;
                } else {
                    die('invalid line option');
                }
                break;
            default:
                die('DEFLECTOR SHEILDS UP');
        }
    }
}

if(isset($_POST['admin']) && ($_POST['admin'] == 1)) {
    file_put_contents($_REQUEST['brand'].'_'.$_REQUEST['product'].'_'.$_REQUEST['model'].'.json', json_encode($options));
} else {
    file_put_contents($_REQUEST['brand'].'_'.$_REQUEST['product'].'_'.$_REQUEST['model'].'_user.json', json_encode($options));
}
echo 'saved!';