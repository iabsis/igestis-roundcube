<?php

/**
 * fetchmail_rc is a plugin that allows users 
 * to check some differents accounts directly in their roudcube application
 * 
 * @Think to add the 'fetchmail_rc' in the $rcmail_config['plugins'] array on the main.inc.php file
 * in order to activate this plugin
 *
 * @author Gilles Hemmerlé (iabsis) <giloux@gmail.com> 
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl.html
 */

class fetchmail_rc extends rcube_plugin {
    /**
     * @var string $task
     */
    public $task = 'settings';
    
    /**
     * @var string Current action
     */
    private $action = "";
    /**
     *
     * @var rcmail Rcmail object
     */
    private $rcmail = null;
    
    /**
     * Launched during the plugin initialization (like a constructor)
     */
    public function init(){     
        // Save the rcmail object on the current one
        $this->rcmail = rcmail::get_instance();
        // Helper for the rcmail action property
        $this->action = $this->rcmail->action;
        
        // Add the Filter menu on the root preferences section (from JS)
        $this->add_texts('localization/', array('accounts', 'manageaccounts'));
        $this->include_script('fetchmail_rc.js');
        
        // Set differents actions
        $this->register_action('plugin.fetchmail_rc', array($this, 'init_html')); 
    }
    
    /**
     * Initialize the plugin html
     */
    public function init_html() {        
        
        // Start the requested action
        switch($this->action) {            
            case "plugin.fetchmail_rc" :
            default :
                $this->api->output->set_pagetitle($this->gettext('accounts'));
		$this->api->output->send('fetchmail_rc.fetchmail_rc');
                break;
        }
    }
    
}