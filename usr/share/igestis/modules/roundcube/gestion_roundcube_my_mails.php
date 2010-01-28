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

// Gestion des identitées pour roundcube
$sql = "SELECT * FROM ROUNDCUBE_users WHERE username='" . $application->userprefs['login'] . "'";
$req = mysql_query($sql) or $application->message_die(mysql_error() . $sql);
$round_users = mysql_fetch_array($req);

if(!$round_users) {
	$sql = "INSERT INTO ROUNDCUBE_users(username, mail_host, created, language) VALUES(
			'" . $application->userprefs['login'] . "',
			'localhost', 
			'" . date("Y-m-d H:i:s") . "',
			'fr_FR')";
	$req = mysql_query($sql) or $application->message_die(mysql_error() . $sql);
	$user_id = mysql_insert_id();
	
	$sql = "INSERT INTO ROUNDCUBE_identities (standard, name, email, user_id) VALUES (
				'1', 
				'" . $application->userprefs['user_label'] . "',
				'" . $application->userprefs['email'] . "',
				'" . $user_id . "')";
	$req = mysql_query($sql) or $application->message_die(mysql_error() . $sql);

}
else {
	$sql = "UPDATE ROUNDCUBE_identities SET email='" . $application->userprefs['email'] . "'
			WHERE user_id IN(SELECT user_id FROM ROUNDCUBE_users WHERE username='" . $application->userprefs['login'] . "') AND
			email LIKE '" . $application->userprefs['login'] . "@localhost'";
	$req = mysql_query($sql) or $application->message_die(mysql_error() . $sql);
}

$application->add_vars(array(
	"login" => $application->userprefs['login'],
	"password" => roundcube_hex_convert($application->userprefs['ssh_password'])
));


// Create content :
$CONTENT = $application->get_html_content("roundcube_gestion_my_mails.htm");
if(!$CONTENT) $application->message_die("Unable to find the html page");



################## Create the content of the page #################################
		
$replace = array("MENU" => $application->generate_menu(), "GENERAL_TITLE" => $GENERAL_TITLE);
$application->add_vars($replace);
$application->show_content($CONTENT);




?>