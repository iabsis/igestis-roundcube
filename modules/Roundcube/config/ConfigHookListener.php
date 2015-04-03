<?php

/**
 * Hook listener for the roundcube module
 *
 * @author Gilles Hemmerlé
 */
namespace Igestis\Modules\Roundcube;

class ConfigHookListener implements \Igestis\Interfaces\HookListenerInterface  {
    public static function listen($HookName, \Igestis\Types\HookParameters $params = null) {
        switch ($HookName) {
            case "beforeContactLdapSave":
                \Igestis\Utils\Debug::addDump("$HookName catched");
                return true;
                break;
            case "afterContactLdapSave" :
                \Igestis\Utils\Debug::addDump("$HookName catched");
                return true;
                break;
            case "loginSuccess" :
                /* @var $logedContact \CoreContacts */
                $logedContact = $params->get("logedContact");
                $password = $params->get("postPassword");
                $_SESSION['roundcubeAuthkey'] = \Igestis\Utils\Encryption::EncryptString($params->get("postLogin") . "\n" . $password . "\n" . uniqid());
                \Igestis\Utils\Debug::addDump("$HookName catched");
                return true;
                break;
            case "afterLogout" :
                // Delete the roudcube session
                if(empty($_COOKIE['roundcube_sessid'])) return true;
                
                $context = \Application::getInstance();
                $currentRoundcubeSession = $context->entityManager->getRepository("RoundcubeSession")->find($_COOKIE['roundcube_sessid']);
                if($currentRoundcubeSession != NULL) {
                    $context->entityManager->remove($currentRoundcubeSession);
                    $context->entityManager->flush();
                    setcookie ("roudcube_sessid", "", time() - 100000);
                }
                
                return true;                
                break;
        
            default:
                break;
        }
        
        return false;
    }
}