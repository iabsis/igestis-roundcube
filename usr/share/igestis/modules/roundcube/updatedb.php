<?
session_start();

include "../../config.php";
include "config.php";
include "lib_roundcube.php";
include "../../includes/common_librairie.php";

// Initialization of the application
$application = new application();

/*
$_GET = _mysql_real_escape_string($_GET);
$_POST = _mysql_real_escape_string($_POST, array("action_argument", "rule_text"));
$_COOKIE = _mysql_real_escape_string($_COOKIE);
*/

if(!$application->is_loged) {// Loged or not loged, that's the question.
    $application->login_form();
}

#################### Gestion des ROUNDCUBE_folders #########################################################

if($_GET['section'] == "roundcube_mail_account" || $_POST['section'] == "roundcube_mail_account") {
    if($_POST['action'] == "edit") {
        $BASE_FOLDER =  create_smb_url();
        $fetchmailrc = file($BASE_FOLDER . "/.fetchmailrc");
        if(is_array($fetchmailrc)) {
            for($i = 0; $i < count($fetchmailrc); $i++) {
                $parsed_line = parse_fetchmail_line($fetchmailrc[$i]);
                if($parsed_line['address'] == $_POST['current_server_name'] && $parsed_line['user'] == $_POST['current_server_username']) {
                    // poll lascaux.local with protocol IMAP user "test1234" password test is gillesh keep
                    $fetchmailrc[$i] = "poll " . $_POST['server_name'] . " with protocol " . $_POST['server_protocol'] . " user " . $_POST['server_username'] . " password ";
                    if(ereg("'", $_POST['server_password'])) $fetchmailrc[$i] .= '"' . $_POST['server_password'] . '"';
                    elseif(ereg('"', $_POST['server_password'])) $fetchmailrc[$i] .= '"' . $_POST['server_password'] . '"';
                    elseif(ereg(' ', $_POST['server_password'])) $fetchmailrc[$i] .= '"' . $_POST['server_password'] . '"';
                    else $fetchmailrc[$i] .= $_POST['server_password'];

                    if(!$_POST['server_keep'] && eregi("keep", $parsed_line['options'])) $parsed_line['options'] = trim(str_replace("keep", "", $parsed_line['options']));
                    if($_POST['server_keep']  && !eregi("keep", $parsed_line['options'])) $parsed_line['options'] = trim($parsed_line['options'] . " keep");

                    if(!$_POST['server_ssl'] && eregi("ssl", $parsed_line['options'])) $parsed_line['options'] = trim(str_replace("ssl", "", $parsed_line['options']));
                    if($_POST['server_ssl']  && !eregi("ssl", $parsed_line['options'])) $parsed_line['options'] = trim($parsed_line['options'] . " ssl");

                    $fetchmailrc[$i] .= " " . $parsed_line['options'];
                }
            }

            if(!wizz::already_wizzed(WIZZ_ERROR)) {
                $fetchmailrc = trim(implode("\n", $fetchmailrc));
                $f = fopen($BASE_FOLDER . "/.fetchmailrc", 'w+');
                fwrite($f, $fetchmailrc);
                fclose($f);
            }
            
        }

        if(!wizz::already_wizzed(WIZZ_ERROR)) new wizz("Compte enregistré", WIZZ_SUCCESS);
        die ("<script language='javascript'>window.opener.location.reload(true); window.close();</script>") ;
    }

    if($_POST['action'] == "new") {
        $BASE_FOLDER = create_smb_url();

        $fetchmailrc = "poll " . $_POST['server_name'] . " with protocol " . $_POST['server_protocol'] . " user " . $_POST['server_username'] . " password ";
        if(ereg("'", $_POST['server_password'])) $fetchmailrc .= '"' . $_POST['server_password'] . '"';
        elseif(ereg('"', $_POST['server_password'])) $fetchmailrc .= '"' . $_POST['server_password'] . '"';
        elseif(ereg(' ', $_POST['server_password'])) $fetchmailrc .= '"' . $_POST['server_password'] . '"';
        else $fetchmailrc .= $_POST['server_password'];
        if($_POST['server_keep']) $fetchmailrc .= " keep";
        if($_POST['server_ssl']) $fetchmailrc .= " ssl";
        $f = @fopen($BASE_FOLDER . "/.fetchmailrc", 'w+');
        @fwrite($f, "\n" . $fetchmailrc);
        @fclose($f);
        if(!wizz::already_wizzed(WIZZ_ERROR)) new wizz("Fichier édité avec succès", WIZZ_SUCCESS, null, 3);
        die ("<script language='javascript'>window.opener.location.reload(true); window.close();</script>") ;
    }


    if($_GET['action'] == "del") {
        $BASE_FOLDER = create_smb_url();

        if(is_file($BASE_FOLDER . "/.fetchmailrc")) {
            $fetchmailrc = file($BASE_FOLDER . "/.fetchmailrc");

            $new_fetchmail_rc = NULL;
            if(is_array($fetchmailrc)) {
                for($i = 0; $i < count($fetchmailrc); $i++) {
                    $parsed_line = parse_fetchmail_line($fetchmailrc[$i]);
                    if($parsed_line) {
                        if($parsed_line['address'] != $_GET['server_name'] || $parsed_line['user'] != $_GET['server_username']) {
                            $new_fetchmail_rc[] = $fetchmailrc[$i];
                        }
                    }
                }
            }

            if(is_array($new_fetchmail_rc)) {
                $new_fetchmail_rc = trim(implode("\n", $new_fetchmail_rc));
                $f = @fopen($BASE_FOLDER . "/.fetchmailrc", 'w+');
                @fwrite($f, $new_fetchmail_rc);
                @fclose($f);
            }
            else {
                @unlink($BASE_FOLDER . "/.fetchmailrc");
                if(smb::is_file($BASE_FOLDER . "/.fetchmailrc")) new wizz("{LANG_ROUNDCUBE_Unable_to_delete_fetchmail_file}");
            }

            
        }

        if(!wizz::already_wizzed(WIZZ_ERROR)) new wizz("Fichier édité avec succès", WIZZ_SUCCESS, null, 3);
        header("location:" . $_GET['page_url']);
        exit;
    }
} ############################################################################################################

if($_POST['section'] == "vacation_message") {
    if($_POST['email'] && !is_email($_POST['email'])) $application->message_die("Format d'email incorrect");

    $BASE_FOLDER =  create_smb_url();

    $f = fopen($BASE_FOLDER . "/.Maildir/.message.txt", "w");
    fwrite($f, str_replace('\r', "\r", str_replace('\n', "\n", $_POST['vacation_message'])));
    fclose($f);

    $f = fopen($BASE_FOLDER . "/.procmail-vacation", "w");
    if($_POST['vacation_message_activated']) {
        fwrite($f,
                'EMAIL="' . $application->userprefs['user_label'] . ' <' . $_POST['email'] . '>"' .  "\n\n" .
                ':0 Whc: $HOME/.vacation.lock' .  "\n" .
                '* !^FROM_DAEMON' .  "\n" .
                '* !^List-' .  "\n" .
                '* !^(Mailing-List|Approved-By|BestServHost|Resent-(Message-ID|Sender)):' .  "\n" .
                '* !^Sender: (.*-errors@|owner-)' .  "\n" .
                '* !^X-[^:]*-List:' .  "\n" .
                '* !^X-(Authentication-Warning|Loop|Sent-To|(Listprocessor|Mailman)-Version):' .  "\n" .
                '* !$^From +$LOGNAME(@| |$)' .  "\n" .
                '| /usr/bin/formail -rD 8192 $HOME/.vacation.cache' .  "\n" .
                ':0 ehc' .  "\n" .
                '| (/usr/bin/formail -rI"Precedence: junk" \\' .  "\n" .
                ' -A"X-Loop: $EMAIL" ; \\' .  "\n" .
                'cat $MAILDIR/.message.txt ) | $SENDMAIL -t');
    }
    else {
        fwrite($f, " ");
        //unlink($BASE_FOLDER . "/.vacation.cache");
    }
    fclose($f);

    header("location:" . urldecode($_POST['page_url']));
    exit;
} ############################################################################################################

if($_POST['section'] == "mail_rules" || $_GET['section'] == "mail_rules") {

    if($_POST['action'] == "edit") {
        $BASE_FOLDER = create_smb_url();

        if(is_array($_POST['what'])) {

            $parser = new MailRules($BASE_FOLDER . "/.procmail-roundcube");
            $parser->reset_rules();

            for($i = 0; $i < count($_POST['what']); $i++) {
                $what = $_POST['what'][$i];
                $rule_type = $_POST['rule_type'][$i];
                $rule_text = $_POST['rule_text'][$i];
                $action_type = $_POST['action_type'][$i];
                $action_argument = $_POST['action_argument'][$i];

                if($what && $rule_type && $rule_text && $action_type && $action_argument) {
                    $parser->add_rule($what, $rule_type, $rule_text, $action_type, $action_argument);
                }
            }

            $parser->write_file($BASE_FOLDER . "/.procmail-roundcube");
        }

        header("location:" . urldecode($_POST['page_url']));
        exit;
    }

    if($_GET['action'] == "del") {
        $BASE_FOLDER = create_smb_url();

        $parser = new MailRules($BASE_FOLDER . "/.procmail-roundcube");

        $what = $_GET['what'];
        $rule_type = $_GET['rule_type'];
        $rule_text = $_GET['rule_text'];
        $action_type = $_GET['action_type'];
        $action_argument = $_GET['action_argument'];

        if($what && $rule_type && $rule_text && $action_type && $action_argument) {
            $parser->delete_rule($what, $rule_type, $rule_text, $action_type, $action_argument);
        }

        $parser->write_file($BASE_FOLDER . "/.procmail-roundcube");


        header("location:" . urldecode($_GET['page_url']));
        exit;
    }

}

@mysql_close();

?>