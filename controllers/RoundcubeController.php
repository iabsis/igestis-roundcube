<?php

namespace Igestis\Modules\Roundcube;

/**
 * Main controller of the roundcube module
 *
 * @author Gilles Hemmerlé
 */
class RoundcubeController extends \IgestisController {
    
    public function indexAction() {
        if(empty($_SESSION['roundcubeAuthkey'])) {
            $password = \Igestis\Utils\Encryption::DecryptString($this->context->security->contact->getSshPassword(), false);
            $_SESSION['roundcubeAuthkey'] = \Igestis\Utils\Encryption::EncryptString($this->context->security->contact->getLogin(). "\n" . $password . "\n" . uniqid());
        }
        $roundcubeKey = $_SESSION['roundcubeAuthkey'] . "\n";
        $roundcubeKey .= date("Y-m-d H:i:s");
        $this->context->render("RoundcubeIndex.twig", array('igestisAuthKey' => \Igestis\Utils\Encryption::EncryptString($roundcubeKey)));
    }
    
}