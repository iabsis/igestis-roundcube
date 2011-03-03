<?

// If a little malicious guy attempt to launche this file directly, application stop with the message below ...
if(!defined("INDEX_LAUNCHED")&& !defined("GENERAL_INDEX_REQUEST_LAUNCHED")) die("Hacking attempt");

if($application->userprefs['user_type'] == "employee") {
    // Création du contenu de l'applet uniquement pour les employés

    // Ajout d'une occurrence de l'applet dans le getionnaire d'applet
    $applet_id = $this->add_applet("ROUNDCUBE_VACATION", "Mon message d'absence", TRUE, false);

    $data = "";
    $file = $application->get_html_content("plugins/roundcube_vacation.htm");
    $data = $file;

    $BASE_FOLDER = create_smb_url();

    $content = "";
    $content = file_get_contents($BASE_FOLDER . "/.procmail-vacation");

    if(trim($content)) $application->add_var("vacation_message_activated", true);

    $data = $application->show_content($data, true);
    // Ajout du contenu de l'applet
    $this->set_applet_data($applet_id, $data);
}



?>