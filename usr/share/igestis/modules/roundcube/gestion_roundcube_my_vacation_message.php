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

$content = "";
if(is_file($BASE_FOLDER . "/.procmail-vacation")) {
	$content = file_get_contents($BASE_FOLDER . "/.procmail-vacation");
}
if(trim($content)) 
{
	$application->add_var("vacation_message_activated", true);
	preg_match("/<([\W\w]+)>/", $content, $matches);
	$application->add_var("email", $matches[1]);
}
else {
	$application->add_var("email", $application->userprefs['email']);
}

$message = "";
if(is_file($BASE_FOLDER . "/.Maildir/.message.txt")) {
	$message = file_get_contents($BASE_FOLDER . "/.Maildir/.message.txt");
}

$application->add_var("vacation_message", htmlentities($message, ENT_NOQUOTES, "UTF-8"));

// Create content :
$CONTENT = $application->get_html_content("roundcube_gestion_my_vacation_message.htm");
if(!$CONTENT) $application->message_die("Unable to find the html page");
$application->set_page_title("{LANG_ROUNDCUBE_MESSAGE_Vacation}");


exec("smbumount /var/home/" . $application->userprefs['login']  . "/");
################## Create the content of the page #################################
		
$replace = array("MENU" => $application->generate_menu());
$application->add_vars($replace);
$application->show_content($CONTENT);




?>