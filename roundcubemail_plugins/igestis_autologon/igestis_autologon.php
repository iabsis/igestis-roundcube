<?php
/**
 * Sample plugin to try out some hooks.
 * This performs an automatic login if accessed from localhost
 *
 * @license GNU GPLv3+
 * @author Thomas Bruederli
 */
class igestis_autologon extends rcube_plugin
{
  public $task = 'login';

  function init()
  {
    $this->add_hook('startup', array($this, 'startup'));
    $this->add_hook('authenticate', array($this, 'authenticate'));
  }

  function startup($args)
  {
    $rcmail = rcmail::get_instance();

    // change action to login
    if (empty($_SESSION['user_id']) && !empty($_GET['_igestis_auth_key']))
      $args['action'] = 'login';
    return $args;
  }

  function authenticate($args)
  {
      $rcmail = rcmail::get_instance();
      $igestisFolder = realpath(dirname(__FILE__) . "/../../../../");
      if(!is_dir($igestisFolder)) {
          return $args;
      }
      else {
          require_once $igestisFolder . "/config/igestis/ConfigIgestisGlobalVars.php";
          require_once $igestisFolder . "/includes/coreClasses/Utils/Encryption.php";
      }

      list($authKey, $datetime) = explode("\n", \Igestis\Utils\Encryption::DecryptString($_GET['_igestis_auth_key']));
      
      $now = new DateTime();
      $passed = new DateTime($datetime);
      $difference = $now->diff($passed);
      $diffSec = ($difference->y * 365 * 24 * 60 * 60) +
                 ($difference->m * 30  * 24 * 60 * 60) +
                 ($difference->d * 24  * 60 * 60) +
                 ($difference->h * 60  * 60)+
                  $difference->s;      

    if (!empty($_GET['_igestis_auth_key']) && $diffSec < 5) {
      $customAuth = explode("\n", \Igestis\Utils\Encryption::DecryptString($authKey));
      var_dump($customAuth);
      $args['user'] = $customAuth[0];
      $args['pass'] = $customAuth[1];
      $args['host'] = 'localhost';
      $args['cookiecheck'] = false;
      $args['valid'] = true;
    }
  
    return $args;
  }

}

