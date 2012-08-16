<?php

/**
 * Description of ConfigControllers
 *
 * @author Gilles Hemmerlé
 */

namespace Igestis\Modules\Roundcube;

class ConfigControllers extends \IgestisConfigController {
    //put your code here
    
    public static function get() {
        return  array(
            /*********** Routes for the Roundcube module ***********/
            array(
                "id" => "roundcube_index",
                "Parameters" => array(
                    "Module" => "roundcube",
                    "Action" => "home"
                ),
                "Controller" => "\Igestis\Modules\Roundcube\RoundcubeController",
                "Action" => "indexAction",
                "Access" => array("ROUNDCUBEV2:EMP")
            ),
            
            
         );
    }
}