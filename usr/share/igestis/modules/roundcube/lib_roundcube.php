<?php

require_once SERVER_FOLDER . "/" . APPLI_FOLDER . "/modules/file_manager/lib_files_manager.php";

function remove_dir($current_dir) {
    if($dir = @opendir($current_dir)) {
        while (($f = readdir($dir)) !== false) {
            if($f > '0' and filetype($current_dir.$f) == "file") {
                if(!unlink($current_dir.$f)) return false;
            } elseif($f > '0' and filetype($current_dir.$f) == "dir") {
                if(!remove_dir($current_dir.$f."\\")) return false;
            }
        }
        closedir($dir);
        if(!rmdir($current_dir)) return false;
    }

    return true;
} ####################################################################################################################################


function parse_fetchmail_line($line) {
    $line = trim($line);
    if(!$line) return false;
    preg_match("/^poll[ ]+([\W\w]+) [\W\w]+(proto|protocol) ([\W\w]+) (user|username) ([\W\w]+) (pass|password) ([\W\w]+)/", $line, $matches);

    $return = array(
            "address" => trim(str_replace("'", '', str_replace('"', '', $matches[1]))),
            "protocol" => trim(str_replace("'", '', str_replace('"', '', $matches[3]))),
            "user" => trim(str_replace("'", '', str_replace('"', '', $matches[5])))
    );

    $options = trim($matches[7]) . " ";
    if(substr($options, 0,1) == '"') {
        preg_match('#^"([\W\w]+)"#sU', $options, $matches);
        $options = str_replace($matches[0], "", $options);
        $return['password'] = $matches[1];
    }
    elseif(substr($options, 0,1) == "'") {
        preg_match("#^'([\W\w]+)'#sU", $options, $matches);
        $options = str_replace($matches[0], "", $options);
        $return['password'] = $matches[1];
    }
    else {
        preg_match("#^([\W\w]+) #sU", $options, $matches);
        $options = str_replace($matches[0], "", $options);
        $return['password'] = $matches[1];
    }

    $return['options'] = $options;

    return $return;

} ####################################################################################################################################


class MailRules {
    private $rules_count;
    private $block_list;
    private $current_block;
    private $file_name;

    function MailRules($file) {
        $this->rules_count = 0;
        $this->current_block = 0;
        $this->file_name = $file;
        if(is_file($file)) $this->open_file($file);
    }

    private function open_file($file) {
        if(!smb::is_file($file)) return false;
        $lines = @file($file);
        $buffer = NULL;

        if(is_array($lines)) {
            foreach($lines as $line) {
                if(substr(trim($line), 0, 2) == ":0") {
                    if($buffer) $this->block_list[]["lines"] = $buffer;
                    $buffer = NULL;
                    $buffer[] = trim($line);
                }
                else {
                    if(trim($line)) $buffer[] .= trim($line);
                }
            }
        }
        if(is_array($buffer)) $this->block_list[]["lines"] = $buffer;
        $this->rules_count = count($this->block_list);

        // We complete this array
        if(is_array($this->block_list)) {
            for($i = 0; $i < count($this->block_list); $i++) {
                for($j = 0; $j < count($this->block_list[$i]["lines"]); $j++) {
                    if(substr($this->block_list[$i]["lines"][$j], 0, 1) == "*") {
                        if(eregi("Subject", $this->block_list[$i]["lines"][$j])) $this->block_list[$i]['options']['what'] = "subject";
                        if(eregi("From", $this->block_list[$i]["lines"][$j])) $this->block_list[$i]['options']['what'] = "From";
                        if(eregi("To", $this->block_list[$i]["lines"][$j])) $this->block_list[$i]['options']['what'] = "To";

                        if(ereg("\.\*", $this->block_list[$i]["lines"][$j])) $this->block_list[$i]['options']['rule_type'] = "has";
                        else $this->block_list[$i]['options']['rule_type'] = "is";
                        //* ^To:

                        $this->block_list[$i]['options']['rule_text'] = trim(preg_replace("/^\* \^[A-Za-z0-9\-\_]+:/", "", $this->block_list[$i]["lines"][$j]));
                        if(substr($this->block_list[$i]['options']['rule_text'], 0, 2) == ".*") $this->block_list[$i]['options']['rule_text'] = substr($this->block_list[$i]['options']['rule_text'], 2);
                        if(substr($this->block_list[$i]['options']['rule_text'], strlen($this->block_list[$i]['options']['rule_text']) - 2) == ".*") {
                            $this->block_list[$i]['options']['rule_text'] = substr($this->block_list[$i]['options']['rule_text'], 0, strlen($this->block_list[$i]['options']['rule_text']) - 2);
                        }
                        // Ajout des \ devant les caractères spéciaux ...
                        $this->block_list[$i]['options']['rule_text'] = $this->decode_regex($this->block_list[$i]['options']['rule_text']);
                    }

                    if($j == count($this->block_list[$i]["lines"]) - 1) {
                        if(substr($this->block_list[$i]["lines"][$j], 0, 1) == "!") {
                            $this->block_list[$i]['options']['action_type'] = "forward";
                            $this->block_list[$i]['options']['action_argument'] = trim(str_replace("!", "", $this->block_list[$i]["lines"][$j])) ;
                        }
                        else {
                            $this->block_list[$i]['options']['action_type'] = "move";
                            $this->block_list[$i]['options']['action_argument'] = trim($this->block_list[$i]["lines"][$j]) ;
                            $this->block_list[$i]['options']['action_argument'] = str_replace('$DEFAULT/.', '', $this->block_list[$i]['options']['action_argument']);
                            if(substr($this->block_list[$i]['options']['action_argument'], strlen($this->block_list[$i]['options']['action_argument'])-1, 1) == "/") {
                                $this->block_list[$i]['options']['action_argument'] =
                                        substr($this->block_list[$i]['options']['action_argument'], 0, strlen($this->block_list[$i]['options']['action_argument'])-1);
                            }
                        }
                    }
                }

            }
        }
        // Array completed
        if(isset($this->block_list) && is_array($this->block_list)) @reset($this->block_list);
    } ///////////////////////////

    public function next_block() {
        if($this->current_block > count($this->block_list)) return false;
        $block = $this->block_list[$this->current_block];
        $this->current_block++;

        return $block;
    } ///////////////////////////

    public function reset_rules() {
        $this->rules_count = 0;
        $this->current_block = 0;
        $this->block_list = NULL;
    }

    public function add_rule($what, $rule_type, $rule_text, $action_type, $action_argument) {
        if($this->is_rule($what, $rule_type, $rule_text, $action_type, $action_argument)) return false;

        if($action_type == "move") $action_argument .= "/";
        $this->block_list[]['options'] = array(
                "what" => $what,
                "rule_type" => $rule_type,
                "rule_text" => $rule_text,
                "action_type" => $action_type,
                "action_argument" => $action_argument
        );

        return true;
    } ///////////////////////////


    private function is_rule($what, $rule_type, $rule_text, $action_type, $action_argument) {
        if(is_array($this->block_list)) {
            foreach($this->block_list as $block) {
                if( strtolower($block['options']['what']) == strtolower($what) &&
                        $block['options']['rule_type'] == $rule_type &&
                        $block['options']['rule_text'] == $rule_text &&
                        $block['options']['action_type'] == $action_type &&
                        $block['options']['action_argument'] == $action_argument ) return true;
            }
        }
        return false;
    } ///////////////////////////


    public function write_file($file=NULL) {
        if(!$file) $file = $this->file_name;

        $file_content = "";

        if(is_array($this->block_list)) {
            foreach($this->block_list as $block) {
                if(!$block['options']) continue;

                $file_content .= ":0\n";
                $file_content .= "* ^" . ucfirst(strtolower($block['options']['what'])) . ": ";
                if($block['options']['rule_type'] == "has") $file_content .= ".*";
                $file_content .= $this->encode_regex($block['options']['rule_text']);
                if($block['options']['rule_type'] == "has") $file_content .= ".*";
                $file_content .= "\n";
                if($block['options']['action_type'] == "forward") {
                    // Transférer le mail à une adresse
                    $file_content .= "! " . $block['options']['action_argument'];
                }
                else {
                    // Déplacer le mail dans le dossier ...
                    if(substr($block['options']['action_argument'], strlen($block['options']['action_argument'])-1, 1) != "/") {
                        $block['options']['action_argument'] .= "/";
                    }
                    $file_content .= '$DEFAULT/.' . $block['options']['action_argument'];
                }

                $file_content .= "\n\n";
            }
        }

        if($file_content) {
            $f = fopen($file, "w+");
            if($f) fwrite($f, $file_content);
            @fclose($f);
        }
        else {
            if(smb::is_file($file)) @unlink($file);
        }
        
    }

    public function delete_rule($what, $rule_type, $rule_text, $action_type, $action_argument) {
        if(is_array($this->block_list)) {
            for($i = 0; $i < count($this->block_list); $i++) {
                if( strtolower($this->block_list[$i]['options']['what']) == strtolower($what) &&
                        $this->block_list[$i]['options']['rule_type'] == $rule_type &&
                        $this->block_list[$i]['options']['rule_text'] == $rule_text &&
                        $this->block_list[$i]['options']['action_type'] == $action_type &&
                        $this->block_list[$i]['options']['action_argument'] == $action_argument ) $this->block_list[$i]['options'] = NULL;
            }
        }
    }

    private function encode_regex($string) {
        $entities = array("\\", "[", "^", '$', ".", '|', '?', '*', '+', '(', ')');
        $replacement = array();
        foreach($entities as $entity) $replacement[] = "\\" . $entity;

        return str_replace($entities, $replacement, $string);
    }

    private function decode_regex($string) {
        $entities = array("[", "^", '$', ".", '|', '?', '*', '+', '(', ')', "\\");
        $replacement = array();
        
        foreach($entities as $entity) $replacement[] = "\\" . $entity;

        return str_replace($replacement, $entities, $string);
    }

} ####################################################################################################################################

// LIster un dossier
function list_folder($folder_to_browse, &$application, $block_name, $date) {
    $BASE_FOLDER = ISHARE_TAPE_FOLDER . "/" . $date . "/";
    if(!is_dir($BASE_FOLDER . $folder_to_browse)) return false;

    $tb_directory = NULL;
    $tb_file = NULL;

    exec("sudo roundcube list-file-folder " . escapeshellarg($BASE_FOLDER . $folder_to_browse), $files_list);

    if(is_array($files_list)) {
        foreach($files_list as $file) {
            if(substr($file, 0, 1) == "D") $tb_directory[] = substr($file, 2);
            if(substr($file, 0, 1) == "F") $tb_file[] = substr($file, 2);
        }
    }

    if ($tb_directory) sort($tb_directory, SORT_STRING);
    if ($tb_file) sort($tb_file, SORT_STRING);

    for ($i = 0; $i < count($tb_directory); $i++) {
        ($cpt++%2 == 0) ? $class = "ligne1" : $class = "ligne2" ;

        $vars = array(
                "CLASS" => $class,
                "folder_name" => htmlentities($tb_directory[$i], NULL, "UTF-8"),
                "folder" => urlencode($folder_to_browse . "/" . $tb_directory[$i]),
                "size" => "&nbsp;",
                "type" => "Folder",
                "icon" => get_icon(),
                // "folder" => $folder_to_browse. "/",
                "file_name" => str_replace("/", "",$tb_directory[$i]),
                "file_url" => $folder_to_browse. "/" . str_replace("/", "",$tb_directory[$i]));

        $application->add_block($block_name, $vars);
    }

    for ($i = 0; $i < count($tb_file); $i++) {
        ($cpt++%2 == 0) ? $class = "ligne1" : $class = "ligne2" ;

        $vars = array(
                "CLASS" => $class,
                "is_file" => true,
                "folder_name" => htmlentities($tb_file[$i], NULL, "UTF-8"),
                "folder" => urlencode($folder_to_browse . "/" . $tb_file[$i]),
                "type" => strtoupper(extension($tb_file[$i])),
                "icon" => get_icon(strtolower(extension($tb_file[$i]))),
                // "folder" => $folder_to_browse. "/",
                "file_name" => str_replace("/", "",$tb_file[$i]),
                "file_url" => $folder_to_browse. "/" . str_replace("/", "",$tb_file[$i]));

        $application->add_block($block_name, $vars);
    }

    return true;
}


function roundcube_hex_convert($string) {
    $return = NULL;
    if($string) {
        for($i = 0; $i < strlen($string); $i++) {
            $char = dechex(ord($string[$i]));
            if(strlen($char) == 1) $char = "0" . $char;
            $return .= $char;
        }
    }
    return $return;
}


?>