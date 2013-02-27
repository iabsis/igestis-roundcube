<?php
/**
 * fetchMailRcList is a list of fetchMailRc objects
 * 
 * @author Gilles Hemmerlé <giloux@gmail.com>
 */

require_once dirname(__FILE__) . "/fetchMailRc.php";

class fetchMailRcList implements Iterator {
    
    /**
     * Cursor position
     * @var int 
     */
    private $position = 0;
    
    /**
     *
     * @var fetchMailRc[]  List of fetchMailRc
     */
    private $datas = array();
    
    private $dbm;


    /**
     * Get all the fetchmail account configured (can specify a user id to retrieve only account for a single user)
     * @param MDB2 $dbm database connection
     * @param int $user_id
     */
    public function __construct($dbm, $user_id=-1) {
        $this->dbm = $dbm;
        $this->position = 0;
        // Retrieve the accounts list
        if($user_id != -1) {
            $sql_result = $this->dbm->query(
                "SELECT * FROM " . get_table_name('fetchmail_rc') . " WHERE fk_user=?",
                $user_id
            );
        }
        else {
            $sql_result = $this->dbm->query(
                "SELECT * FROM " . get_table_name('fetchmail_rc')
            );
        }
        
        while ($account = $this->dbm->fetch_assoc($sql_result)) {
            $fetchmailRc = new fetchMailRc();
            $fetchmailRc->from_array($account);
            $this->datas[] = $fetchmailRc;
        }
        
    }


    public function current() {
        return $this->datas[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function valid() {
        return isset($this->datas[$this->position]);
    }
}