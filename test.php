<?php

$admin = isset($_REQUEST['admin']) ? true : false;

require_once 'includes/provisioner/samples/json.php';


DEFINE('PROVISIONER_PATH', 'includes/provisioner/');
DEFINE('BRAND', 'cisco');
DEFINE('PRODUCT', 'spa5xx');
DEFINE('MODEL', 'SPA504G');

$only_show = array();

//Get admin data if exists
if (file_exists(BRAND . '_' . PRODUCT . '_' . MODEL . '.json')) {
    $data = file_get_contents(BRAND . '_' . PRODUCT . '_' . MODEL . '.json');
    $saved_data = json_decode($data, TRUE);
    if (!$admin) {
        foreach ($saved_data['admin'] as $key => $data) {
            $only_show[] = $key;
        }
    }
}

//Get user data if exists
if (file_exists(BRAND . '_' . PRODUCT . '_' . MODEL . '_user.json')) {
    $data = file_get_contents(BRAND . '_' . PRODUCT . '_' . MODEL . '_user.json');
    $user_data = json_decode($data, TRUE);
}

include('includes/generate_gui.class');

$gui = new generate_gui();

$output = $gui->create_template_array(BRAND, PRODUCT, MODEL);

$dont_show = array(
    'option|dial_plan',
    'option|background_type',
    'option|logo_type',
    'option|picture_url',
    'option|enable_webserver',
    'option|enable_webserver_admin',
    'option|station_name',
    'option|date_format',
    'option|ring1',
    'option|ring2',
    'option|ring3',
    'option|ring4',
    'option|ring5',
    'option|ring6',
    'option|ring7',
    'option|ring8',
    'option|ring9',
    'option|ring10'
);

echo '<form method="post" action="go.php" id="add">';
echo '<table>';

foreach ($output['data'] as $sections => $data) {
    $show_sections = TRUE;
    foreach ($data as $subsections => $sub_data) {
        $show_subsections = TRUE;
        foreach ($sub_data as $variables => $sub_variables) {
            foreach ($sub_variables as $variable_key => $html_els) {
                if ($variable_key == '0') {
                    $var = $variables;
                } else {
                    $var = $variables . '_' . $variable_key;
                }
                if ($html_els['default_value'] != 'corn') {

                    $user_value = NULL;
                    if (isset($saved_data)) {
                        $user_data = isset($user_data) ? $user_data : $saved_data;
                        preg_match('/(.*)\|(.*)/', $var, $matches);
                        switch ($matches[1]) {
                            case 'loop':
                                if (preg_match('/(.*)_(.*)_(.*)/', $matches[2], $matches2)) {
                                    $user_value = isset($user_data['data'][$matches2[1]][$matches2[2]][$matches2[3]]) ? $user_data['data'][$matches2[1]][$matches2[2]][$matches2[3]] : $saved_data['data'][$matches2[1]][$matches2[2]][$matches2[3]];
                                }
                                break;
                            case 'option':
                                $user_value = isset($user_data['data'][$matches[2]]) ? $user_data['data'][$matches[2]] : $saved_data['data'][$matches2[1]][$matches2[2]][$matches2[3]];
                                break;
                            case 'lineloop':
                                die('COME BACK TO THIS');
                                break;
                            default:
                                die('AHH');
                                break;
                        }
                    }

                    //Left over breaks that can be safely ignored (basically corn)
                    $output = $gui->generate_html($html_els, $var, $user_value, $only_show, $dont_show);
                    if ($output !== FALSE) {
                        echo $show_sections ? "<tr><td colspan='2'><h1>" . $sections . "</h1></td></tr>" : '';
                        echo $show_subsections ? "<tr><td colspan='2'><h2>" . $subsections . "</h2></td></tr>" : '';

                        $admin_out = $admin ? '<input type="checkbox" name="admin|' . $var . '" value="Bike" /> Allow Users to edit this' : '';

                        echo '<tr><td>' . $output . '</td><td>' . $admin_out . '</td></tr>';

                        $show_sections = FALSE;
                        $show_subsections = FALSE;
                    }
                } else {
                    
                }
            }
        }
    }
}
echo '</table>';

//ghetto hack, will fix later
echo '<input type="hidden" name="admin" value="' . $admin . '"/>';
echo '<input type="hidden" name="brand" value="' . BRAND . '"/>';
echo '<input type="hidden" name="product" value="' . PRODUCT . '"/>';
echo '<input type="hidden" name="model" value="' . MODEL . '"/>';
echo '<input type="submit" value="Save" />';
echo '</form>';

