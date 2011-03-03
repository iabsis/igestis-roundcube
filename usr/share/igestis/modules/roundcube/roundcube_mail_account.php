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

$application->set_page_title("{LANG_ROUNDCUBE_New_ROUNDCUBE_mail_account_description_short}");

switch($_GET['action'])
{
	case "edit" :			
		$CONTENT = $application->get_html_content("roundcube_mail_account.htm");
		if(!$CONTENT) $application->message_die("{LANG_Unable_to_find_the_roundcube_mail_account_form}");
		$server_infos = NULL;

                $smb_link = create_smb_url();
                $f = fopen($smb_link . "/.fetchmailrc", 'r');
                if(!$f) new wizz("Unable to open the fetchmailrc file !");
                else {
                    $file_fund = true;
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
		
		if($file_fund && !$server_infos) $application->message_die("Unable to find this line in your fetchmailrc", true);
		
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

$application->add_vars(array("MENU" => $application->generate_menu(), "XAJAX" => $script_xajax));
$application->show_content($CONTENT);


?>