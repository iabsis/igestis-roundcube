<?php

namespace Igestis\Modules\Roundcube;

/**
 * Description of ConfigMenu
 *
 * @author Gilles Hemmerlé
 */
class ConfigMenu implements \IgestisImplementsConfigMenu {
    public static function set(\IgestisMenu $menu) {
        echo "passe";
    }
}