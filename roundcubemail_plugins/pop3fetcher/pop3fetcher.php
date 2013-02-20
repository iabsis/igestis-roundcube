<?php
/**
 * TEST PLUGIN
 *
 * SPERIAMO DI IMPARARE IN FRETTA COME FUNZIONANO QUESTI PLUGIN...
 *
 * @version 1.0
 * @author Paolo Moretti
 */
class pop3fetcher extends rcube_plugin
{
	public $config = Array(
		"max_forwarded_message_size" => 4194304
	);
	public $task = 'mail|settings';
    public $time_between_checks = 60;
	public $skin ='';
	
  
function init(){
    $this->rcmail = rcmail::get_instance();
	$skin  = $this->rcmail->config->get('skin');
    $this->add_texts('localization/');  
    $this->include_script('pop3fetcher.js');
    
	// abort if there are no css adjustments
    if(!file_exists('plugins/pop3fetcher/skins/' . $skin . '/pop3fetcher.css')){
		if(!file_exists('plugins/pop3fetcher/skins/default/pop3fetcher.css'))
			$this->skin = "default";
		else
			$this->skin = "default";
    }
	
	$this->add_hook('render_page', array($this, 'render_page'));
    $this->register_action('plugin.pop3fetcher', array($this, 'navigation'));
	
    $this->add_hook('preferences_sections_list', array($this, 'settings_link'));
    $this->add_hook('preferences_list', array($this, 'settings'));
	
    $this->add_hook('template_object_pop3fetcher_form_edit', array($this, 'accounts_edit'));
    $this->add_hook('template_object_pop3fetcher_form_add', array($this, 'accounts_add'));
	
	if($_GET['_action']=='check-recent'||$_GET['_action']=='plugin.checkunread'){
		define('DISPLAY_XPM4_ERRORS', false); // display XPM4 errors
		// path to 'POP3.php' file from XPM4 package
      	require_once './plugins/pop3fetcher/XPM4/POP35.php';
      	require_once './plugins/pop3fetcher/XPM4/FUNC5.php';
		
		$user_id = $this->rcmail->user->data['user_id'];  
		$query = "SELECT * FROM " . get_table_name('pop3fetcher_accounts') . " WHERE user_id=?";
		$ret = $this->rcmail->db->query($query, $user_id);
		$accounts = array();
		while($account = $this->rcmail->db->fetch_assoc($ret))
			$accounts[] = $account;
		$temparr = array();
		// QUESTA RIGA SETTA LA CARTELLA SUL SERVER
		// $this->rcmail->storage_init();
		// print_r($this->rcmail->storage->set_folder("INBOX"));
		
		
		foreach($accounts as $key => $val){
			$temparr[$key] = $val['pop3fetcher_email'];
			write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, checkunread for ".$val['pop3fetcher_email']);
			// SCARICO I MESSAGGI DAL POP3
			//print_r($val);
			$last_uidl = $val['last_uidl'];
			$c = POP35::connect($val['pop3fetcher_serveraddress'], $val['pop3fetcher_username'], $val['pop3fetcher_password'], intval($val['pop3fetcher_serverport']), $val['pop3fetcher_ssl'], 100);
			//print_r($_RESULT);
			if($c){
				$s = POP35::pStat($c, false);// or die(print_r($_RESULT));
				
				//$Count - total number of messages, $b - total bytes
				list($Count, $b) = each($s);

				write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, found new messages: $Count, $b");
				$msglist = POP35::puidl($c);
				if(is_array($msglist))
					$Count=count($msglist);
				else
					$Count=0;

				if(sizeof($msglist)>0){
					$i = 0;					
					for ($j = 1; $j <= sizeof($msglist); $j++) {
						//write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, MSG UIDL: ".$msglist["$j"]);
						//write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, LAST UIDL: ".$last_uidl);
					   if ($msglist["$j"] == $last_uidl) {
							$i = $j+1;
							break;
					   }
					}

					if ($Count < $i) {
						POP35::disconnect($c);
					}
					if ($Count == 0) {
						//echo "Login OK: Inbox EMPTY<br />";
						POP35::disconnect($c);
					} else {
						$newmsgcount = $Count - $i + 1;
						//echo "Login OK: Inbox contains [" . $newmsgcount . "] messages<br />";
					}
					$this->rcmail->get_storage();
					$this->rcmail->imap_connect();
					$max_messages_downloaded_x_session=10;
					$max_bytes_downloaded_x_session=1000000;
					$cur_bytes_downloaded=0;
					$k=1; 
					for (; $i < $Count; $i++) {
						if($k<=$max_messages_downloaded_x_session){
							$cur_msg_index=$i+1;
							$l = POP35::pList($c, $cur_msg_index);
							$last_uidl = $msglist["$cur_msg_index"];
							if($l["$cur_msg_index"]<=$this->config["max_forwarded_message_size"]||$this->config["max_forwarded_message_size"]==0){
								$cur_bytes_downloaded=$cur_bytes_downloaded+$l["$i"];
								write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, k=$k Downloading a message with UIDL ".$msglist["$cur_msg_index"].", SIZE: ".$l["$cur_msg_index"]);
								//set_time_limit(20); // 20 seconds per message max
								$Message = POP35::pRetr($c, $cur_msg_index) or die(print_r($_RESULT)); // <- get the last mail (newest)
								write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, k=$k  Downloaded a message with UIDL ".$msglist["$cur_msg_index"]);
								$message_id = $this->rcmail->storage->save_message("INBOX", $Message);
								write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, k=$k Stored a message with UIDL ".$msglist["$cur_msg_index"]);
								$this->rcmail->storage->unset_flag("$message_id", "SEEN");
								if(!($val['pop3fetcher_leaveacopyonserver'])){
									if(POP35::pDele($c, $cur_msg_index))
										write_log("test_pop3fetcher.txt", "INTERCEPT: DELETING MESSAGE $cur_msg_index $last_uidl");
									else
										write_log("test_pop3fetcher.txt", "INTERCEPT: ERROR: CANNOT DELETE $last_uidl");
								} else {
									write_log("test_pop3fetcher.txt", "INTERCEPT: leaveacopyonserver IS SET");
								}
								if($cur_bytes_downloaded>$max_bytes_downloaded_x_session)
									$i=$Count+1;
							} else {
								write_log("test_pop3fetcher.txt", "INTERCEPT: Skipped message $last_uidl ");
							}
							write_log("test_pop3fetcher.txt", "INTERCEPT: trying to update DB: $last_uidl ".$val['pop3fetcher_id']);
							$query = "UPDATE " . get_table_name('pop3fetcher_accounts') . " SET last_uidl=? WHERE pop3fetcher_id=?";
							$ret = $this->rcmail->db->query($query, $last_uidl, $val['pop3fetcher_id']);
							write_log("test_pop3fetcher.txt", "INTERCEPT: updated DB: $last_uidl ".$val['pop3fetcher_id']);
							$k=$k+1;
						} else {
							$i=$Count+1;
						}
					}
				}
				POP35::disconnect($c);
			} else {
				write_log("test_pop3fetcher.txt", "INTERCEPT: task mail, POP35::connectfailed for ".$val['pop3fetcher_email']);
			}
		}
	} 
}
  
public function render_page($params){
	$this->include_stylesheet('skins/default/pop3fetcher.css');
	return($params);

}
  
function navigation(){
	if(isset($_GET['_edit'])){
		$this->edit();
	}
	if(isset($_GET['_add'])){
		$this->add();
	}
	if(isset($_POST['_edit_do'])){
		$this->edit_do();
	}
	if(isset($_POST['_add_do'])){
		$this->add_do();
	}
	if(isset($_POST['_delete_do'])){
		$this->delete_do();
	}
}
	
  function settings_link($args){

	$rcmail = $this->rcmail;
	$user_id = $rcmail->user->data['user_id'];
	$rcmail = rcmail::get_instance();
	$args['list']['pop3fetcher'] = array(
		'id' => 'pop3fetcher',
		'section' => $this->gettext('other_accounts')
	);
    return $args;
  }
  
function settings($args){
	$framed = get_input_value('_framed', RCUBE_INPUT_POST);
	if ($args['section'] == 'pop3fetcher') {
		$args['blocks']['main']['name'] = "";
		$accounts = $this->accounts_get_sorted_list();
		if(count($accounts) > 0){
			$edit = $this->gettext("edit");
			$delete = $this->gettext("delete");
			foreach($accounts as $key => $val){
				$edit_link = "./?_task=settings&_action=plugin.pop3fetcher&_edit=1&_pop3fetcher_id=" . $val['pop3fetcher_id'] . "&_framed=1";
				$delete_link = "./?_task=settings&_action=plugin.pop3fetcher&_delete=1&_pop3fetcher_id=" . $val['pop3fetcher_id'] . "&_framed=1";
				$content = "<a href=\"$edit_link\"><img alt=\"$edit\" title=\"$edit\" src=\"./plugins/pop3fetcher/skins/$this->skin/images/icons/pencil.png\" /></a>
						   <a href=\"$delete_link\" onclick=\"pop3fetcher_delete_do(this, " . $val['pop3fetcher_id'] . ");return(false);\"><img alt=\"$delete\" title=\"$delete\" src=\"./plugins/pop3fetcher/skins/$this->skin/images/icons/delete.png\" /></a>";
				$args['blocks']['main']['options']['pop3fetcher_account_'.$val['pop3fetcher_id']]['title'] = rep_specialchars_output($val['pop3fetcher_email']);
				$args['blocks']['main']['options']['pop3fetcher_account_'.$val['pop3fetcher_id']]['content'] = $content;
			}
		} else {
			$args['blocks']['main']['options']['no_accounts_found']['title'] = "";
			$args['blocks']['main']['options']['no_accounts_found']['content'] = "";
		}
		if($_GET['_action']=="edit-prefs"){
			$this->include_stylesheet('skins/' . $this->skin . '/pop3fetcher.css');
			$add_account_button = "<div style='padding:0 12px 4px 12px;' class='formbuttons'><input type='button' class='button mainaction' value='" . $this->gettext('add') . "' onclick='document.location.href=\"./?_task=settings&_action=plugin.pop3fetcher&_add=1&_framed=1\"' /></div>";
			$this->rcmail->output->add_footer($add_account_button);
		}
	}
	return($args);
}
  
 
	
function edit(){
	$rcmail = rcmail::get_instance();
	$rcmail->output->send("pop3fetcher.form_edit"); 	
}

function edit_do(){
	$rcmail = rcmail::get_instance();

	$pop3fetcher_id = get_input_value('_pop3fetcher_id', RCUBE_INPUT_POST);
	$pop3fetcher_email = get_input_value('_pop3fetcher_email', RCUBE_INPUT_POST);
	$pop3fetcher_username = get_input_value('_pop3fetcher_username', RCUBE_INPUT_POST);
	$pop3fetcher_password = get_input_value('_pop3fetcher_password', RCUBE_INPUT_POST);
	$pop3fetcher_serveraddress = get_input_value('_pop3fetcher_serveraddress', RCUBE_INPUT_POST);
	$pop3fetcher_serverport = get_input_value('_pop3fetcher_serverport', RCUBE_INPUT_POST);
	$pop3fetcher_ssl = get_input_value('_pop3fetcher_ssl', RCUBE_INPUT_POST);
	$pop3fetcher_leaveacopy = get_input_value('_pop3fetcher_leaveacopy', RCUBE_INPUT_POST);
	
	if($pop3fetcher_leaveacopy=="true"){
		$pop3fetcher_leaveacopy=true;
	} else {
		$pop3fetcher_leaveacopy=false;
	}

	/* QUA VERIFICO LA CONNESSIONE */
	if(!$this->test_connection($pop3fetcher_username,$pop3fetcher_password,$pop3fetcher_serveraddress,$pop3fetcher_serverport,$pop3fetcher_ssl)){
		$rcmail->output->add_label(
			'pop3fetcher.account_unableconnect'
		);
		$this->rcmail->output->command('plugin.edit_do_error_connecting', Array());
        $this->rcmail->output->send('plugin');
	} else {
		//$pop3fetcher_password = $rcmail->encrypt($pop3fetcher_password);
		$query = "UPDATE " . get_table_name('pop3fetcher_accounts') . " SET pop3fetcher_email=?, pop3fetcher_username=?, pop3fetcher_password=?, pop3fetcher_serveraddress=?, pop3fetcher_serverport=?, pop3fetcher_SSL=?, pop3fetcher_leaveacopyonserver=? WHERE pop3fetcher_id=?";
		$ret = $rcmail->db->query($query, $pop3fetcher_email, $pop3fetcher_username, $pop3fetcher_password, $pop3fetcher_serveraddress, $pop3fetcher_serverport, $pop3fetcher_ssl, $pop3fetcher_leaveacopy, $pop3fetcher_id);
		if($ret){
			$this->rcmail->output->command('plugin.edit_do_ok', Array());
			$this->rcmail->output->send('plugin');
		} else {
			$this->rcmail->output->command('plugin.edit_do_error_saving', Array());
			$this->rcmail->output->send('plugin');
		}
	}
}

function accounts_edit($args){
	$rcmail = rcmail::get_instance();
	
	$pop3fetcher_id = get_input_value('_pop3fetcher_id', RCUBE_INPUT_GET);

	$arr = $this->get($pop3fetcher_id);

	// add some labels to client 
	/**/
	$rcmail->output->add_label(
		'pop3fetcher.editaccount',
		'accounts.dnempty',
		'accounts.userempty',
		'accounts.passwordempty',
		'accounts.passwordnotmatch',
		'accounts.hostempty'
	);

	//$out  = "<form onsubmit='return accounts_validate()' method='post' action='./?_task=settings&_action=plugin.accounts&_edit_do=1&_framed=1'>\n";
	$out  = "<form method='post' action='./?_task=settings&_action=plugin.pop3fetcher&_edit_do=1&_framed=1'>\n";
	$out .= $this->accounts_form_content($arr['pop3fetcher_email'], $arr['pop3fetcher_username'], $arr['pop3fetcher_password'], $arr['pop3fetcher_serveraddress'], $arr['pop3fetcher_serverport'], $arr['pop3fetcher_ssl'], $arr['pop3fetcher_leaveacopyonserver']);
	$out .= "<input type='hidden' name='_pop3fetcher_id' id='pop3fetcher_id' value='$pop3fetcher_id' />\n";
	$out .= "<input class='button mainaction' id='edit_do' type='button' value ='" . $this->gettext('submit') . "' />";
	$out .= "<span>&nbsp;</span><input type='button' class='button' value='" . $this->gettext('back') . "' onclick='document.location.href=\"./?_task=settings&_action=edit-prefs&_section=pop3fetcher&_framed=1\"' />\n";
	$out .= "</form>\n";
	$out .= "<script type='text/javascript'>
			$('#edit_do').click(function(){
				pop3fetcher_edit_do();				
			});
			</script>";

	$args['content'] = $out;
	return $args;
}  

function get($pop3fetcher_id=0){
	$rcmail = rcmail::get_instance();
    $user_id = $rcmail->user->data['user_id'];  
	  
	$query = "SELECT * FROM " . get_table_name('pop3fetcher_accounts') . " WHERE pop3fetcher_id=? and user_id=?";

	$ret = $rcmail->db->query($query, $pop3fetcher_id, $user_id);
	$sql = $rcmail->db->fetch_assoc($ret);

	return $sql;
}

function accounts_form_content($email="",$username="",$password="",$server="", $port="", $useSSL='none', $leave_a_copy=false){ 
	$rcmail = rcmail::get_instance();

	// allow the following attributes to be added to the <table> tag
	$attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

	// return the complete edit form as table
	$user = $rcmail->user->data['username'];
	if($_SESSION['global_alias'])
	  $user = $_SESSION['global_alias'];
	  if($email!="")
			$out .= '<fieldset><legend>' . $email . ' ::: ' . $user . '</legend>' . "\n";
		else
			$out .= '<fieldset>' . "\n";
	$out .= '<br />' . "\n";
	$out .= '<table' . $attrib_str . ">\n";

	$field_id = 'pop3fetcher_email';
	$input_pop3fetcher_email = new html_inputfield(array('autocomplete' => 'off', 'name' => '_pop3fetcher_email', 'id' => $field_id, 'size' => 30));
	$out .= sprintf("<tr><td valign=\"middle\" class=\"title\"><label for=\"%s\">%s</label>:</td><td colspan=\"3\">%s</td></tr>\n",
				$field_id,
				rep_specialchars_output($this->gettext('account_email')),
				$input_pop3fetcher_email->show($email));

	$field_id = 'pop3fetcher_username';
	$input_pop3fetcher_username = new html_inputfield(array('autocomplete' => 'off', 'name' => '_pop3fetcher_username', 'id' => $field_id, 'size' => 30));
	$out .= sprintf("<tr><td valign=\"middle\" class=\"title\"><label for=\"%s\">%s</label>:</td><td colspan=\"3\">%s</td></tr>\n",
				$field_id,
				rep_specialchars_output($this->gettext('account_username')),
				$input_pop3fetcher_username->show($username));
				
	$field_id = 'pop3fetcher_password';
	$input_pop3fetcher_password = new html_passwordfield(array('autocomplete' => 'off', 'name' => '_pop3fetcher_password', 'id' => $field_id, 'size' => 30));
	$out .= sprintf("<tr><td valign=\"middle\" class=\"title\"><label for=\"%s\">%s</label>:</td><td colspan=\"3\">%s</td></tr>\n",
				$field_id,
				rep_specialchars_output($this->gettext('account_password')),
				$input_pop3fetcher_password->show($password));	
				
	$field_id = 'pop3fetcher_serveraddress';
	$input_pop3fetcher_serveraddress = new html_inputfield(array('autocomplete' => 'off', 'name' => '_pop3fetcher_serveraddress', 'id' => $field_id, 'size' => 30));
	$out .= sprintf("<tr><td valign=\"middle\" class=\"title\"><label for=\"%s\">%s</label>:</td><td colspan=\"3\">%s</td></tr>\n",
				$field_id,
				rep_specialchars_output($this->gettext('account_serveraddress')),
				$input_pop3fetcher_serveraddress->show($server));
			
	$field_id = 'pop3fetcher_serverport';
	$input_pop3fetcher_serverport = new html_inputfield(array('autocomplete' => 'off', 'name' => '_pop3fetcher_serverport', 'id' => $field_id, 'size' => 10));
	
	$field_id2 = 'pop3fetcher_ssl';
	$input_pop3fetcher_ssl = new html_select(array('name' => '_pop3fetcher_ssl', 'id' => $field_id2));
	$input_pop3fetcher_ssl->add('none', '');
	$input_pop3fetcher_ssl->add('tls', 'tls');
	$input_pop3fetcher_ssl->add('ssl', 'ssl');
	$input_pop3fetcher_ssl->add('sslv2', 'sslv2');
	$input_pop3fetcher_ssl->add('sslv3', 'sslv3');
	$out .= sprintf("<tr><td valign=\"middle\" class=\"title\"><label for=\"%s\">%s</label>:</td><td>%s</td><td valign=\"middle\" class=\"title\"><label for=\"%s\">%s</label>:</td><td>%s</td></tr>\n",
				$field_id,
				rep_specialchars_output($this->gettext('account_serverport')),
				$input_pop3fetcher_serverport->show($port),
				$field_id2,
				rep_specialchars_output($this->gettext('account_usessl')),
				$input_pop3fetcher_ssl->show($useSSL));
		
	$field_id = 'pop3fetcher_leaveacopy';
	$input_pop3fetcher_leaveacopy = new html_checkbox(array('name' => '_pop3fetcher_leaveacopy', 'id' => $field_id));
	$out .= sprintf("<tr><td valign=\"middle\" colspan=\"3\" class=\"title\"><label for=\"%s\">%s</label>:</td><td>%s</td></tr>\n",
				$field_id,
				rep_specialchars_output($this->gettext('account_leaveacopy')),
				$input_pop3fetcher_leaveacopy->show($leave_a_copy?false:true)); // QUESTA COSA E' STRANA MA FUNZIONA...	
		
	$out .= "\n</table>";
	$out .= '<br />' . "\n";
	$out .= "</fieldset>\n";

	return $out;  
}


/* START SECTION ADD ACCOUNT */  

function add(){
	$this->rcmail->output->send("pop3fetcher.form_add");
}

function accounts_add($args){
	$rcmail = $this->rcmail;
	/*
	// add some labels to client
	$rcmail->output->add_label(
	  'accounts.dnempty',
	  'accounts.userempty',
	  'accounts.passwordempty',
	  'accounts.passwordnotmatch',
	  'accounts.hostempty'
	);
	*/

	//$out  = "<form onsubmit='return accounts_validate()' method='post' action='./?_task=settings&_action=plugin.accounts&_add_do=1&_framed=1'>\n";
	$out  = "<form method='post' action='./?_task=settings&_action=plugin.pop3fetcher&_add_do=1&_framed=1'>\n";

	$out .= $this->accounts_form_content();

	$out .= "<input type='hidden' name='_add' id='add' value=1 />\n";               
	$out .= "<input class='button mainaction' id='add_do' type='button' value ='" . $this->gettext('submit') . "' />";
	$out .= "<span>&nbsp;</span><input type='button' class='button' value='" . $this->gettext('back') . "' onclick='document.location.href=\"./?_task=settings&_action=edit-prefs&_section=pop3fetcher&_framed=1\"' />\n";
	$out .= "</form>\n";
	$out .= "<script type='text/javascript'>
			$('#add_do').click(function(){
				pop3fetcher_add_do();				
			});
			</script>";

	$args['content'] = $out;

	return $args;
}

function add_do(){
	$rcmail = $this->rcmail;
	
	$pop3fetcher_email = get_input_value('_pop3fetcher_email', RCUBE_INPUT_POST);
	$pop3fetcher_username = get_input_value('_pop3fetcher_username', RCUBE_INPUT_POST);
	$pop3fetcher_password = get_input_value('_pop3fetcher_password', RCUBE_INPUT_POST);
	$pop3fetcher_serveraddress = get_input_value('_pop3fetcher_serveraddress', RCUBE_INPUT_POST);
	$pop3fetcher_serverport = get_input_value('_pop3fetcher_serverport', RCUBE_INPUT_POST);
	$pop3fetcher_ssl = get_input_value('_pop3fetcher_ssl', RCUBE_INPUT_POST);
	$pop3fetcher_leaveacopy = get_input_value('_pop3fetcher_leaveacopy', RCUBE_INPUT_POST);
	
	if($pop3fetcher_leaveacopy=="true"){
		$pop3fetcher_leaveacopy=true;
	} else {
		$pop3fetcher_leaveacopy=false;
	}

	$user_id = $rcmail->user->data['user_id'];

	/* QUA VERIFICO LA CONNESSIONE */
	if(!$this->test_connection($pop3fetcher_username,$pop3fetcher_password,$pop3fetcher_serveraddress,$pop3fetcher_serverport,$pop3fetcher_ssl)){
		$rcmail->output->add_label(
			'pop3fetcher.account_unableconnect'
		);
		$this->rcmail->output->command('plugin.add_do_error_connecting', Array());
        $this->rcmail->output->send('plugin');
	} else {
		require_once './plugins/pop3fetcher/XPM4/POP35.php';
      		require_once './plugins/pop3fetcher/XPM4/FUNC5.php';
		// SALVO L'UID DELL'ULTIMO MESSAGGIO, IN MODO DA SCARICARE SOLO I NUOVI
		$c = POP35::connect($pop3fetcher_serveraddress, $pop3fetcher_username, $pop3fetcher_password, intval($pop3fetcher_serverport), $pop3fetcher_ssl, 100);
		$last_uidl=0;
		if($c){
			$msglist = POP35::puidl($c);
			$last_uidl = $msglist[count($msglist)-1];
			write_log("test_pop3fetcher.txt", "INTERCEPT: TASK ADDACCOUNT, fetching last UID $last_uidl");
		}
		$query = "SELECT * FROM " . get_table_name('pop3fetcher_accounts') . " WHERE user_id=? AND pop3fetcher_email=?";
		$ret = $rcmail->db->query($query, $user_id, $pop3fetcher_email);
		$arr = $rcmail->db->fetch_assoc($ret);
		if(!is_array($arr)){
			$query = "INSERT INTO " . get_table_name('pop3fetcher_accounts') . "(pop3fetcher_email, pop3fetcher_username, pop3fetcher_password, pop3fetcher_serveraddress, pop3fetcher_serverport, pop3fetcher_ssl, pop3fetcher_leaveacopyonserver, user_id, last_uidl) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
			$ret = $rcmail->db->query($query,
				$pop3fetcher_email,
				$pop3fetcher_username,
				$pop3fetcher_password,
				$pop3fetcher_serveraddress,
				$pop3fetcher_serverport,
				$pop3fetcher_ssl,
				$pop3fetcher_leaveacopy,
				$user_id,
				$last_uidl);

			if($ret){    
				$this->rcmail->output->command('plugin.add_do_ok', Array());
				$this->rcmail->output->send('plugin');
			} else {
				$this->rcmail->output->command('plugin.add_do_ok', Array());
				$this->rcmail->output->send('plugin');
			}
		} else {
				$this->rcmail->output->command('plugin.add_do_ok', Array());
				$this->rcmail->output->send('plugin');
		}
	}
}
/* END SECTION ADD ACCOUNT */  
  
  function accounts_get_sorted_list()
  {
    $rcmail = rcmail::get_instance();  
    $user_id = $rcmail->user->data['user_id'];
    $accounts = array();
    $query = "SELECT * FROM " . get_table_name('pop3fetcher_accounts') . " WHERE user_id=?";
	$sql = $rcmail->db->query($query, $user_id);
    while($account = $rcmail->db->fetch_assoc($sql))
      $accounts[] = $account;
    $temparr = array();
    foreach($accounts as $key => $val){
      $temparr[$key] = $val['pop3fetcher_email'];
    }
    
    asort($temparr);
   
    $return = array();
    foreach($temparr as $key => $val){
      $return[] = $accounts[$key];
    }
    
    return $return;  
  }
  
	function delete_do(){
		$rcmail = $this->rcmail;
		$user_id = $this->rcmail->user->data['user_id'];  
		$pop3fetcher_id = get_input_value('_pop3fetcher_id', RCUBE_INPUT_POST);
		$query = "DELETE FROM " . get_table_name('pop3fetcher_accounts') . " WHERE user_id=? and pop3fetcher_id=?";
		$sql = $rcmail->db->query($query, $user_id, $pop3fetcher_id);
		$this->rcmail->output->command('plugin.delete_do_ok', Array());
		$this->rcmail->output->send('plugin');
	}

  function test_connection($pop3fetcher_username,$pop3fetcher_password,$pop3fetcher_serveraddress,$pop3fetcher_serverport,$pop3fetcher_ssl){
		define('DISPLAY_XPM4_ERRORS', false); // display XPM4 errors
		require_once './plugins/pop3fetcher/XPM4/POP35.php';
      		require_once './plugins/pop3fetcher/XPM4/FUNC5.php';
		$pop3fetcher_serverport=intval($pop3fetcher_serverport);
		write_log("test_pop3fetcher.txt", "TESTING CONNECTION: $pop3fetcher_username, $pop3fetcher_password, $pop3fetcher_serveraddress, $pop3fetcher_serverport, $pop3fetcher_ssl");
		if($pop3fetcher_serverport>0&&FUNC5::is_hostname($pop3fetcher_serveraddress, true)){
			$c = POP35::connect($pop3fetcher_serveraddress, $pop3fetcher_username, $pop3fetcher_password, $pop3fetcher_serverport, $pop3fetcher_ssl, 100,NULL, false);
			if($c){
				write_log("test_pop3fetcher.txt", "TESTING CONNECTION: SUCCESS $pop3fetcher_serveraddress:$pop3fetcher_serverport");
				POP35::disconnect($c, false);
				return(true);
			} else {
				write_log("test_pop3fetcher.txt", "TESTING CONNECTION: FAIL $pop3fetcher_serveraddress:$pop3fetcher_serverport");
				return(false);
			}
		} else {
			write_log("test_pop3fetcher.txt", "TESTING CONNECTION: FAIL $pop3fetcher_serveraddress:$pop3fetcher_serverport");
			return(false);
		}
  }
}
?>