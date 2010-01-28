<?

// If a little malicious guy attempt to launche this file directly, application stop with the message below ...
if(!defined("INDEX_LAUNCHED")&& !defined("GENERAL_INDEX_REQUEST_LAUNCHED")) die("Hacking attempt");
define(IABSIS_NEWS_URL, "http://www.iabsis.com/index.php?mact=CGFeedMaker,cntnt01,default,0&cntnt01feed=News2&cntnt01showtemplate=false&cntnt01returnid=15");


// Ajout d'une occurrence de l'applet dans le getionnaire d'applet
$applet_id = $this->add_applet("ROUNDCUBE_VACATION", "Mon message d'absence", TRUE);


// Création du contenu de l'applet
$data = "";
$file = $application->get_html_content("plugins/roundcube_vacation.htm");
$data = $file;

$pwd = decrypt_string($application->userprefs['ssh_password']);
exec("smbmount //`hostname`.local/" . $application->userprefs['login']  . " /var/home/" . $application->userprefs['login']  . "/ -o username=" . $application->userprefs['login']  . ",password=\"$pwd\"");
$BASE_FOLDER = "/var/home/" . $application->userprefs['login'] ;

$content = "";
if(is_file($BASE_FOLDER . "/.procmail-vacation")) {
	$content = file_get_contents($BASE_FOLDER . "/.procmail-vacation");
}
if(trim($content)) $application->add_var("vacation_message_activated", true);
exec("smbumount /var/home/" . $application->userprefs['login']  . "/");

$data = $application->show_content($data, true);
// Ajout du contenu de l'applet
$this->set_applet_data($applet_id, $data);


?>