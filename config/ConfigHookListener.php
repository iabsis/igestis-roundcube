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
        
            default:
                break;
        }
        
        return false;
    }
}