<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of webprov
 *
 * @author Andrew
 */
class webprov {
    public $path;
    public $tpl;
    
    function __construct() {
        //include freepbx configuration   
        
        $this->path = dirname(__FILE__)."/";
                
	require($this->path."rain.tpl.class.php");

	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "tpl/" );
	raintpl::configure("cache_dir", "tmp/" );

	//initialize a Rain TPL object
	$this->tpl = new RainTPL;
        
     
    }
    
    function get_devices($selected = NULL) {
        $brand = 'cisco';
        $product = 'spa5xx';
        $dev_list = $this->xml2array($this->path.'provisioner/endpoint/'.$brand.'/'.$product.'/family_data.xml');
        $dev_list = $dev_list['data']['model_list'];
        foreach($dev_list as $key => $data) {
            if($selected == $data['model']) {
                $dev_list[$key]['selected'] = TRUE;
            }
        }
        return($dev_list);
    }
    
    function add_device($mac,$device,$ext,$name) {
        
    }
    
    /**
     * xml2array() will convert the given XML text to an array in the XML structure.
     * @author http://www.bin-co.com/php/scripts/xml2array/
     * @param <type> $url The XML file
     * @param <type> $get_attributes 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
     * @param <type> $priority Can be 'tag' or 'attribute'. This will change the way the resulting array structure. For 'tag', the tags are given more importance.
     * @param <type> $array_tags - any tag names listed here will allways be returned as an array, even if there is only one of them.
     * @return <type> The parsed XML in an array form. Use print_r() to see the resulting array structure.
     */
    function xml2array($url, $get_attributes = 1, $priority = 'tag', $array_tags=array()) {
        $contents = "";
        if (!function_exists('xml_parser_create')) {
            return array();
        }
        $parser = xml_parser_create('');
        if (!($fp = @ fopen($url, 'rb'))) {
            return array();
        }
        while (!feof($fp)) {
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) {
            return; //Hmm...
        }
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array;
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
            }
            if ($type == "open") {
                $parent[$level - 1] = & $current;
		if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
		    if (in_array($tag,$array_tags)) {
                        $current[$tag][0] = $result;
                        $repeated_tag_index[$tag . '_' . $level]=1;
                    	$current = & $current[$tag][0];
		    } else {
			$current[$tag] = $result;
			if ($attributes_data) {
				$current[$tag . '_attr'] = $attributes_data;
			}
			$repeated_tag_index[$tag . '_' . $level] = 1;
			$current = & $current[$tag];
		   }
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } else if ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } else if ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }
}
