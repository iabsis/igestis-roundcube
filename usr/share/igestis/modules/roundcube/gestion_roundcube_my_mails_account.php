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

// Montage du dossier personnel
$smb_link = create_smb_url();

if(!smb::is_file($smb_link . "/.fetchmailrc")) {
    $f = @fopen($smb_link . "/.fetchmailrc", "w+");
    @fwrite($f, " ");
    @fclose($f);
}


$f = @fopen($smb_link . "/.fetchmailrc", 'r');

if(!$f) new wizz("Unable to open the fetchmailrc file !");
else {
    while(!feof($f)) {
        $line = fgets($f, 512);
        if($parsed_line = parse_fetchmail_line($line)) {
            $application->add_block("ROUNDCUBE_mail_server_LIST", array(
                "CLASS" => ($cpt++%2 ? "ligne1" : "ligne2"),
                "server_name" => $parsed_line['address'],
                "server_username" => $parsed_line['user'],
            ));
        }
    }
    @fclose($f);
}


// Create content :
$CONTENT = $application->get_html_content("roundcube_gestion_my_mails_account.htm");
if(!$CONTENT) $application->message_die("Unable to find the html page");
$application->set_page_title("{LANG_ROUNDCUBE_My_Email_Account}");



exec("smbumount /var/home/" . $application->userprefs['login']  . "/");
################## Create the content of the page #################################
		
$replace = array("MENU" => $application->generate_menu());
$application->add_vars($replace);
$application->show_content($CONTENT);




?>