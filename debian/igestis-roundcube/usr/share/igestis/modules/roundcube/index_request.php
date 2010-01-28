<?
if(!defined('GENERAL_INDEX_REQUEST_LAUNCHED')) die("Hacking attempt");

function unable_vacation_message() {
	global $application;
	
	$objResponse = new xajaxResponse();
	$BASE_FOLDER = mount_user_folder("ajax");
	
	$f = fopen($BASE_FOLDER . "/.procmail-vacation", "w");
	fclose($f);
	
	//if(is_file($BASE_FOLDER . "/.vacation.cache")) @unlink($BASE_FOLDER . "/.vacation.cache");
	exec("smbumount /var/home/" . $application->userprefs['login']  . "/");
	
	return $objResponse;
}



function roundcube_test_mail_account($protocol, $server, $user, $password, $ssl) {
	global $application;
	
	$objResponse = new xajaxResponse();
	$BASE_FOLDER = mount_user_folder("ajax");
	
	if($return != 0) {
		$script ="messageObj.setHtmlContent('" . str_replace("'", "\\'", implode("<br />",$string)) . "'); messageObj.display();";
		$objResponse->AddScript($script);
		return $objResponse;
	}
	
	$f = @fopen($BASE_FOLDER . "/.fetchmailrc-tmp", "w");
	if(!$f) {
		$script ="messageObj.setHtmlContent('Unable to create the file $BASE_FOLDER/.fetchmailrc-tmp<br /><br /><b><a href=\"#\" onclick=\"messageObj.close()\">Fermer</a></b>'); messageObj.display();";
		$objResponse->AddScript($script);
		return $objResponse;
	}
	$fetchmailrc = "poll " . $server . " with protocol " . $protocol . " user " . $user . " password ";
	if(ereg("'", $password)) $fetchmailrc .= '"' . $password . '"';
	elseif(ereg('"', $password)) $fetchmailrc .= '"' . $password . '"';
	elseif(ereg(' ', $password)) $fetchmailrc .= '"' . $password . '"';
	else $fetchmailrc .= $password;
	
	if($ssl) $fetchmailrc .= " ssl";
	
	fwrite($f, $fetchmailrc);	
	fclose($f);
	
	
	exec('sudo ishare fetchmail ' . escapeshellarg($application->userprefs['login']), $output, $return);
	if(is_file($BASE_FOLDER . "/.fetchmailrc-tmp")) @unlink($BASE_FOLDER . "/.fetchmailrc-tmp");
	exec("smbumount /var/home/" . $application->userprefs['login']  . "/");
	
	$script ="messageObj.setHtmlContent('" . str_replace("'", "\\'", implode("<br />", $output)) . '<br /><br /><b><a href="#" onclick="messageObj.close()">Fermer</a></b>' . "'); messageObj.display();";
	$objResponse->AddScript($script);
	
	return $objResponse;


}

?>