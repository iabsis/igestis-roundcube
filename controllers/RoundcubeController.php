<?php

namespace Igestis\Modules\Roundcube;

/**
 * Main controller of the roundcube module
 *
 * @author Gilles Hemmerlé
 */
class RoundcubeController extends \IgestisController {
    
    public function indexAction() {
        $roundcubeKey = $_SESSION['roundcubeAuthkey'] . "\n";
        $roundcubeKey .= date("Y-m-d H:i:s");
        $this->context->render("RoundcubeIndex.twig", array('igestisAuthKey' => \Igestis\Utils\Encryption::EncryptString($roundcubeKey)));
    }
    
}