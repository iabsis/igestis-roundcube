<?
define("ROUNDCUBE_FOLDERS", "/home/samba/data/");

$MODULE_NAME = "roundcube"; 

$roundcube['module_name'] = "{LANG_ROUNDCUBE_MODULE_NAME}";

$roundcube['rights_list'][0]['RIGHT_NAME'] = "{LANG_ROUNDCUBE_RIGHT_NONE}";
$roundcube['rights_list'][0]['RIGHT_CODE'] = "NONE";

$roundcube['rights_list'][1]['RIGHT_NAME'] = "{LANG_ROUNDCUBE_RIGHT_EMP}";
$roundcube['rights_list'][1]['RIGHT_CODE'] = "EMP";


$roundcube['module_menu_name']['title'][0] = "{LANG_ROUNDCUBE_My_mails_account}";
$roundcube['module_menu_name']['script_file'][0] = "gestion_roundcube_my_mails_account.php";
$roundcube['module_menu_name']['client_access'][0] = false;
$roundcube['module_menu_name']['employee_access'][0] = array("ADMIN", "EMP"); 

$roundcube['module_menu_name']['title'][1] = "{LANG_ROUNDCUBE_Vacation_message}";
$roundcube['module_menu_name']['script_file'][1] = "gestion_roundcube_my_vacation_message.php";
$roundcube['module_menu_name']['client_access'][1] = false;
$roundcube['module_menu_name']['employee_access'][1] = array("ADMIN", "EMP");

$roundcube['module_menu_name']['title'][2] = "{LANG_ROUNDCUBE_Mails_rules}";
$roundcube['module_menu_name']['script_file'][2] = "gestion_roundcube_my_mails_rules.php";
$roundcube['module_menu_name']['client_access'][2] = false;
$roundcube['module_menu_name']['employee_access'][2] = array("ADMIN", "EMP");

$roundcube['module_menu_name']['title'][3] = "{LANG_ROUNDCUBE_MY_MAILS_TITLE}";
$roundcube['module_menu_name']['script_file'][3] = "gestion_roundcube_my_mails.php";
$roundcube['module_menu_name']['client_access'][3] = false;
$roundcube['module_menu_name']['employee_access'][3] = array("ADMIN", "EMP");

?>