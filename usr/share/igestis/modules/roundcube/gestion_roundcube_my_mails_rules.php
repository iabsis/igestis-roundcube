<?
 
// If a little malicious guy attempt to launche this file directly, application stop with the message below ...
if(!defined("INDEX_LAUNCHED")) die("Hacking attempt");

include SERVER_FOLDER . "/" . APPLI_FOLDER . "/modules/roundcube/config.php";
include SERVER_FOLDER . "/" . APPLI_FOLDER . "/modules/roundcube/lib_roundcube.php";

$roundcube_access = $application->module_access("roundcube");
if($roundcube_access != "ADMIN" && $roundcube_access != "EMP")
{// FOr the employee, just access for the admins and techs users
	$application->message_die("You have not access to this page");
}

$BASE_FOLDER = mount_user_folder("main");
$parser = new MailRules($BASE_FOLDER . "/.procmail-roundcube");


while($block = $parser->next_block()) {
	$application->add_block("ROUNDCUBE_rules_LIST", array(
		"CLASS" => ($cpt++%2 ? "ligne1" : "ligne2"),
		"ID" => $cpt,
		"WHAT_" . strtoupper($block['options']['what']) => true,
		"RULE_TYPE_" . strtoupper($block['options']['rule_type']) => true,
		"rule_text" => htmlentities($block['options']['rule_text'], ENT_COMPAT, "UTF-8"),
		"ACTION_TYPE_" . strtoupper($block['options']['action_type']) => true,
		"action_argument" => htmlentities($block['options']['action_argument'], ENT_COMPAT, "UTF-8")
	));
}

// Create content :
$CONTENT = $application->get_html_content("roundcube_gestion_my_mails_rules.htm");
if(!$CONTENT) $application->message_die("Unable to find the html page");
$application->set_page_title("{LANG_ROUNDCUBE_MESSAGE_Mail_Rules}");


exec("smbumount /var/home/" . $application->userprefs['login']  . "/");
################## Create the content of the page #################################
		
$replace = array("MENU" => $application->generate_menu());
$application->add_vars($replace);
$application->show_content($CONTENT);




?>