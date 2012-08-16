<?php

namespace Igestis\Modules\Roundcube;

/**
 * Main controller of the roundcube module
 *
 * @author Gilles Hemmerlé
 */
class RoundcubeController extends \IgestisController {
    
    public function indexAction() {
        $this->context->render("RoundcubeIndex.twig", array());
    }
    
}