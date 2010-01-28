<?
session_start();
 
include "../../config.php";
include "../../includes/common_librairie.php";
include "../../includes/xajax/xajax.inc.php";
require_once("../../index_common.php");
include "lib_roundcube.php";

// Initialization of the application
$application = new application();

if(!$application->is_loged)
{// Loged or not loged, that's the question.
	die("You are not loged");
}

########################  !! Access to this page !! ###############################
$roundcube_access = $application->module_access("roundcube");
if($roundcube_access != "ADMIN" && $roundcube_access != "EMP")
{// FOr the employee, just access for the admins and techs users
	$application->message_die("You have not access to this page");
}

###################################################################################

$BASE_FOLDER = mount_user_folder("popup");

switch($_GET['action'])
{
	case "edit" :			
		$CONTENT = $application->get_html_content("roundcube_mail_account.htm");
		if(!$CONTENT) $application->message_die("{LANG_Unable_to_find_the_roundcube_mail_account_form}");
		$server_infos = NULL;
		
		if(is_file($BASE_FOLDER . "/.fetchmailrc")) {
			$f = fopen($BASE_FOLDER . "/.fetchmailrc", 'r');
			if(!$f) die("Unable to open the fetchmailrc file !");
			while(!feof($f)) {
				$line = fgets($f, 512);
				if($parsed_line = parse_fetchmail_line($line)) {
					if($parsed_line['address'] == $_GET['server_name'] && $parsed_line['user'] == $_GET['server_username']) {
						$server_infos = $parsed_line;
						break;
					}					
				}		
			}	
			fclose($f);			
		}
		else $application->message_die("Unable to find the fetchmailrc file", true);
		
		if(!$server_infos) $application->message_die("Unable to find this line in your fetchmailrc", true);
		
		$application->add_vars(array(
				"action" => "edit",
				"server_name" => $server_infos['address'],
				"server_username" => $server_infos['user'],
				"protocol_" . $server_infos['protocol'] => true,
				"server_password" => $server_infos['password'],
				"server_keep" => eregi("keep", $server_infos['options']), 
				"server_ssl" => eregi("ssl", $server_infos['options'])
		));
		break;
	
	default :
		// Create content :
		$CONTENT = $application->get_html_content("roundcube_mail_account.htm");
		if(!$CONTENT) $application->message_die("{LANG_Unable_to_find_the_roundcube_mail_account_form}");
		
		$application->add_vars(array("action" => "new"));

		
		break;
}

exec("smbumount /var/home/" . $application->userprefs['login']  . "/");

################## Create the content of the page #################################

$application->add_vars(array("MENU" => $application->generate_menu(), "GENERAL_TITLE" => $GENERAL_TITLE, "XAJAX" => $script_xajax));
$application->show_content($CONTENT);


?>