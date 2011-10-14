<?php

DEFINE('PROVISIONER_PATH', 'includes/provisioner/');

include('includes/generate_gui.class');

$gui = new generate_gui();

$output = $gui->create_template_array('cisco','spa5xx','SPA504G');

//$show_only = array('loop|lineops_1_displaynameline','loop|lineops_2_displaynameline');
$show_only = array();

$admin = TRUE;

echo '<form method="post" id="add">';
        echo '<table>';

foreach($output['data'] as $sections => $data) {
    $show_sections = TRUE;
    foreach ($data as $subsections => $sub_data) {
        $show_subsections = TRUE;
        foreach ($sub_data as $variables => $sub_variables) {
            foreach($sub_variables as $variable_key => $html_els) {  
                if ($variable_key == '0') {
                    $var = $variables;
                } else {
                    $var = $variables . '_' . $variable_key;
                }
                                
                if($html_els['default_value'] != 'corn') { //Left over breaks that can be safely ignored (basically corn)
                    $output = $gui->generate_html($html_els,$var,$show_only);
                    if($output !== FALSE) {
                        echo $show_sections ? "<tr><td colspan='2'><h1>" . $sections . "</h1></td></tr>" : '';
                        echo $show_subsections ? "<tr><td colspan='2'><h2>" . $subsections . "</h2></td></tr>" : '';
                        
                        $admin_out = $admin ? '<input type="checkbox" name="admin|'.$var.'" value="Bike" /> Allow Users to edit this' : '';
                        
                        echo '<tr><td>'.$output.'</td><td>'.$admin_out.'</td></tr>';

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


echo '<input type="submit" value="Save" />';
echo '</form>';

