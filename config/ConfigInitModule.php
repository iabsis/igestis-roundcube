<?php

namespace Igestis\Modules\Roundcube;


/**
 * Description of ConfigMenu
 *
 * @author Gilles Hemmerlé
 */
class ConfigInitModule implements \Igestis\Interfaces\ConfigMenuInterface, \Igestis\Interfaces\ConfigRightsListInterface {
    /**
     * Return the right list used by the IgestisSecurity object to let it know which are all the rights of the differents section of the application
     * @return array Rights list 
     */
    public static function getRightsList() {
        $module =   array(
            "MODULE_NAME" => ConfigModuleVars::$moduleName,
            "MODULE_FULL_NAME" => _(ConfigModuleVars::$moduleShowedName),
            "RIGHTS_LIST" => NULL);
        
        $rights = array(
            array(
                "CODE" => "NONE",
                "NAME" => "None",
                "DESCRIPTION" => "The user has no access to this module"
            ),
            array(
                "CODE" => "EMP",
                "NAME" => "Employee",
                "DESCRIPTION" => "The user can access the webmail to check his mails"
            )            
        );
        
        $module['RIGHTS_LIST'] = $rights;
        
        return $module;
    }

    public static function menuSet(\application $context, \IgestisMenu &$menu) {
        $moduleAccess = $context->security->module_access(ConfigModuleVars::$moduleName);     
        if($moduleAccess == "EMP") $menu->addItem(dgettext(ConfigModuleVars::$moduleName, "Communication"), dgettext(ConfigModuleVars::$moduleName, "My emails"), ConfigControllers::createUrl("roundcube_index"));        
    }
}