<?
session_start();

if(!defined(SERVER_FOLDER)) include "../../config.php";
include SERVER_FOLDER . "/" . APPLI_FOLDER . "/includes/common_librairie.php";
include SERVER_FOLDER . "/" . APPLI_FOLDER . "/includes/xajax/xajax.inc.php";
include SERVER_FOLDER . "/" . APPLI_FOLDER . "/modules/roundcube/config.php";
include SERVER_FOLDER . "/" . APPLI_FOLDER . "/modules/roundcube/lib_roundcube.php";

// Is the index file launched ?
define("INDEX_LAUNCHED", true);

// Initialization of the application
$application = new application();


if(!$application->is_loged)
{// Loged or not loged, that's the question.
	$application->login_form();
}

$roundcube_access = $application->module_access("roundcube");
if($roundcube_access != "ADMIN" && $roundcube_access != "EMP")
{// FOr the employee, just access for the admins and techs users
	$application->message_die("You have not access to this page");
}

$application->add_vars(array(
	"login" => $application->userprefs['login'],
	"password" => roundcube_hex_convert($application->userprefs['ssh_password'])
));


// Create content :
$CONTENT = $application->get_html_content("roundcube_roundcube_connexion.htm");
if(!$CONTENT) $application->message_die("Unable to find the html page");



################## Create the content of the page #################################
		
$replace = array("MENU" => $application->generate_menu());
$application->add_vars($replace);
$application->show_content($CONTENT);




?>