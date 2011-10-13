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
    public $db;
    
    function __construct() {
        global $db;
        //include freepbx configuration   
        
        $this->path = dirname(__FILE__)."/";
                
	require($this->path."rain.tpl.class.php");

	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "tpl/" );
	raintpl::configure("cache_dir", "tmp/" );

	//initialize a Rain TPL object
	$this->tpl = new RainTPL;
        $this->db = $db;
        
        if(!$this->table_exists('simple_endpointman_mac_list')) {
            $this->build_tables();
        }
    }
    
    function get_data($id, $row = 'custom', $type = 'mac' ) {
	if ($type === 'line' ) {
		$tablename = "simple_endpointman_line_list";
		$colid = "ext";
		if ($row === 'custom') {
			$colname = "custom_cfg_data";
		} elseif ($row === 'user') {
			$colname = "user_cfg_data";
		} else {
			die ("Unknown line row $row - programmer error");
		}
	} elseif ( $type === 'mac' ) {
		$tablename = "simple_endpointman_mac_list";
		$colid = "mac";
		if ($row === 'custom') {
			$colname = "global_custom_cfg_data";
		} elseif ($row === 'user') {
			$colname = "global_user_cfg_data";
		} else {
			die ("Unknown mac row $row - programmer error");
		}
	} else {
		die ("Unknown get of type $type - programmer error");
	}
	$sql = "SELECT $colname from $tablename where $colid = '$id'";
	$result = json_decode($this->db->getOne($sql), true);
	return $result;
    }

    function set_data($id, $var, $val, $row = 'custom', $type = 'mac' ) {
	if ($type === 'line' ) {
		$tablename = "simple_endpointman_line_list";
		$colid = "ext";
		if ($row === 'custom') {
			$colname = "custom_cfg_data";
		} elseif ($row === 'user') {
			$colname = "user_cfg_data";
		} else {
			die ("Unknown line row $row - programmer error");
		}
	} elseif ( $type === 'mac' ) {
		$tablename = "simple_endpointman_mac_list";
		$colid = "mac";
		if ($row === 'custom') {
			$colname = "global_custom_cfg_data";
		} elseif ($row === 'user') {
			$colname = "global_user_cfg_data";
		} else {
			die ("Unknown mac row $row - programmer error");
		}
	} else {
		die ("Unknown set of type $type - programmer error");
	}
	$existing = $this->get_data($id, $row, $type);
	$existing[$var]=$val;
	$newcontents=json_encode($existing);
	$sql = "UPDATE $tablename SET $colname='$newcontents' where $colid = '$id'";
        return $this->db->query($sql);
    }
	
	
	
    function table_exists($table) {
	global $amp_conf;
        $sql = "SHOW TABLES FROM ".$amp_conf['AMPDBNAME'];
        $result = $this->db->getAll($sql);

        foreach($result as $row) {
            if ($row[0] == $table) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    function get_managed_devices(){
        $sql = "SELECT simple_endpointman_mac_list.*, simple_endpointman_line_list.description, simple_endpointman_line_list.ext FROM simple_endpointman_mac_list, simple_endpointman_line_list WHERE simple_endpointman_mac_list.id = simple_endpointman_line_list.mac_id";
        $final = $this->db->getAll($sql,array(),DB_FETCHMODE_ASSOC);

        return $final;
    }
    
    function build_tables() {
        $sql = "CREATE TABLE IF NOT EXISTS `simple_endpointman_mac_list` (
                  `id` int(10) NOT NULL auto_increment,
                  `mac` varchar(12) default NULL,
                  `model` varchar(11) NOT NULL,
                  `product` varchar(11) NOT NULL,
                  `brand` varchar(11) NOT NULL,
                  `template_id` int(11) NOT NULL,
                  `global_custom_cfg_data` longblob NOT NULL,
                  `global_user_cfg_data` longblob NOT NULL,
                  `config_files_override` text NOT NULL,
                  `global_settings_override` longblob,
                  `specific_settings` longblob,
                  PRIMARY KEY  (`id`),
                  UNIQUE KEY `mac` (`mac`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";
        
        $this->db->query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `simple_endpointman_line_list` (
                  `luid` int(11) NOT NULL auto_increment,
                  `mac_id` int(11) NOT NULL,
                  `line` smallint(2) NOT NULL,
                  `ext` varchar(15) NOT NULL,
                  `description` varchar(20) NOT NULL,
                  `custom_cfg_data` longblob NOT NULL,
                  `user_cfg_data` longblob NOT NULL,
                  PRIMARY KEY  (`luid`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=latin1";
        
        $this->db->query($sql);
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
    
    function genRandomString() {
        $length = 10;
        $alphas = 'abcdefghijklmnopqrstuvwxyz';
        $numas = '0123456789';
        $string = '';
        $string .= $alphas[mt_rand(0, strlen($alphas))];
        $string .= $numas[mt_rand(0, strlen($numas))];
        for ($p = 0; $p < $length; $p++) {
            $string .= $alphas[mt_rand(0, strlen($alphas))];
        }
        $string .= $numas[mt_rand(0, strlen($numas))];
        return $string;
    }
    
    function remove_device($mac) {
	# What exten is this?
	$sql = "select ext from simple_endpointman_line_list l, simple_endpointman_mac_list m where l.mac_id=m.id and m.mac='$mac'";
	dbug($sql);
	$ext = $this->db->getOne($sql);
	print "I found $ext. I'm so happy\n";
	$sql = "delete from simple_endpointman_line_list where ext='$ext'";
	$this->db->query($sql);
	$sql = "delete from simple_endpointman_mac_list where mac='$mac'";
	$this->db->query($sql);
	core_devices_del($ext);
	core_users_del($ext);
	do_reload();
    }

    function add_device($mac,$device,$ext,$name,$vm,$vmpin,$email) {
        $mac = $this->mac_check_clean($mac);
        
        $secret = $this->genRandomString();
        dbug($secret);
        $vars = array(
            'display' => 'extensions',
            'type' => 'setup',
            'action' => 'add',
            'extdisplay' => '',
            'extension' => $ext,
            'name' => $name,
            'cid_masquerade' => '', 
            'sipname' => '',
            'outboundcid' => '', 
            'ringtimer' => 0,
            'cfringtimer' => 0,
            'concurrency_limit' => 0,
            'callwaiting' => 'enabled',
            'answermode' => 'disabled',
            'call_screen' => 0,
            'pinless' => 'disabled',
            'emergency_cid' => '',
            'tech' => 'sip',
            'hardware' => 'generic',
            'qnostate' => 'usestate',
            'newdid_name' => '', 
            'newdid' => '', 
            'newdidcid' => '',
            'devinfo_secret_origional' => '',
            'devinfo_secret' => $secret,
            'devinfo_dtmfmode' => 'rfc2833',
            'devinfo_canreinvite' => 'no',
            'devinfo_context' => 'from-internal',
            'devinfo_host' => 'dynamic',
            'devinfo_trustrpid' => 'yes',
            'devinfo_sendrpid' => 'no',
            'devinfo_type' => 'peer',
            'devinfo_nat' => 'no',
            'devinfo_port' => '5060',
            'devinfo_qualify' => 'yes',
            'devinfo_qualifyfreq' => '60',
            'devinfo_transport' => 'udp',
            'devinfo_encryption' => 'no',
            'devinfo_callgroup' => '',
            'devinfo_pickupgroup' => '',
            'devinfo_disallow' => '',
            'devinfo_allow' => '',
            'devinfo_dial' => '',
            'devinfo_accountcode' => '',
            'devinfo_mailbox' => '',
            'devinfo_vmexten' => '',
            'devinfo_deny' => '0.0.0.0/0.0.0.0',
            'devinfo_permit' => '0.0.0.0/0.0.0.0',
            'noanswer_dest' => 'goto0',
            'busy_dest' => 'goto1',
            'chanunavail_dest' => 'goto2',
            'dictenabled' => 'disabled',
            'dictformat' => 'ogg',
            'dictemail' => '',
            'langcode' => '',
            'record_in' => 'Adhoc',
            'record_out' => 'Adhoc',
            'vm' => 'disabled'
        );
	# But.. is vm REALLY disabled?
	if ($vm == 'yes') { 
		$vm = array (
			'vm' => 'enabled',
			'mailbox' => $ext,
			'vmpwd' => $vmpin,
			'email' => $email,
			'attach' => 'attach=yes',
			'saycid' => 'saycid=no',
			'envelope' => 'envelope=no',
			'delete' => 'delete=no',
			'pager' => '',
			'vmcontext' => 'default',
		);
		$vars = array_merge($vars, $vm);
	}

        $_REQUEST=$vars;

        if($mac) {
            if(core_users_add($vars)) {
                if(core_devices_add($ext, 'sip', '', 'fixed', $ext, $name)) {
                        $sql = "INSERT INTO simple_endpointman_mac_list (mac, model,brand,product) VALUES ('".$mac."', '".$device."', 'cisco', 'spa5xx')";
                        dbug($sql);
                        $this->db->query($sql);

                        $sql = 'SELECT last_insert_id()';
                        $ext_id =& $this->db->getOne($sql);
                        
                        $sql = "INSERT INTO `simple_endpointman_line_list` (`mac_id`, `ext`, `line`, `description`) VALUES ('".$ext_id."', '".$ext."', '1', '".addslashes($name)."')";
                        $this->db->query($sql);
                        
			# Create voicemail
			if ($vars['vm'] === 'enabled') {
				voicemail_mailbox_add($ext, $vars);
			}
			do_reload();
			return true; 
		}
            }
        } 
        return false;
    }
    
    function mac_check_clean($mac) {
        if ((strlen($mac) == "17") OR (strlen($mac) == "12")) {
            //It might be better to use switch here instead of these IF statements...

            //Is the mac separated by colons(:) or dashes(-)?
            if (preg_match("/[0-9a-f][0-9a-f][:-]".
            "[0-9a-f][0-9a-f][:-]".
            "[0-9a-f][0-9a-f][:-]".
            "[0-9a-f][0-9a-f][:-]".
            "[0-9a-f][0-9a-f][:-]".
            "[0-9a-f][0-9a-f]/i", $mac)) {
                return(strtoupper(str_replace(":", "", str_replace("-", "", $mac))));
                //Is the string exactly 12 characters?
            } elseif(strlen($mac) == "12") {
                //Now is the string a valid HEX mac address?
                if (preg_match("/[0-9a-f][0-9a-f]".
                "[0-9a-f][0-9a-f]".
                "[0-9a-f][0-9a-f]".
                "[0-9a-f][0-9a-f]".
                "[0-9a-f][0-9a-f]".
                "[0-9a-f][0-9a-f]/i", $mac)) {
                    return(strtoupper($mac));
                } else {
                    return(FALSE);
                }
                //Is the mac separated by whitespaces?
            } elseif(preg_match("/[0-9a-f][0-9a-f][\s]".
            "[0-9a-f][0-9a-f][\s]".
            "[0-9a-f][0-9a-f][\s]".
            "[0-9a-f][0-9a-f][\s]".
            "[0-9a-f][0-9a-f][\s]".
            "[0-9a-f][0-9a-f]/i", $mac)) {
                return(strtoupper(str_replace(" ", "", $mac)));
            } else {
                return(FALSE);
            }
        } else {
            return(FALSE);
        }
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
