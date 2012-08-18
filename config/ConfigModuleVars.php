<?php
/**
 * This class will permitt to set all global variables of the module
 * @Author : Gilles Hemmerlé <gilles.h@iabsis.com>
 */

namespace Igestis\Modules\Roundcube;

define("ROUNDCUBE_VERSION", "0.1-1");
define("ROUNDCUBE_MODULE_NAME", "Roundcube");
define("ROUNDCUBE_TEXTDOMAIN", ROUNDCUBE_MODULE_NAME .  ROUNDCUBE_VERSION);
/**
 * Configuration of the module
 *
 * @author Gilles Hemmerlé
 */
class ConfigModuleVars {

    /**
     * @var String Numéro de version du module
     */
    public static $version = ROUNDCUBE_VERSION;
    /**
     *
     * @var String Name of the module (used only on the source code) 
     */
    public static $moduleName = ROUNDCUBE_MODULE_NAME;
    
    /**
     *
     * @var String Name of the menu showed to the user
     */
    public static $moduleShowedName = "My emails (v2)";
    
    /**
     *
     * @var String textdomain used for this module
     */
    public static $textDomain = ROUNDCUBE_TEXTDOMAIN;
}
