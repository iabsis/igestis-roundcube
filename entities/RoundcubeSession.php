<?php


/**
 * @Entity
 * @Table(name="ROUNDCUBE_session")
 */
class RoundcubeSession
{
    /**
     * @Id
     * @Column(type="string", name="sess_id")
     */
    private $sessId;
    
    /**
     * @var string $label
     * @Column(type="datetime")
     */
    private $created;
    
    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    private $changed;
    
    /**
     * @Column(type="string")
     * @var string
     */
    private $ip;
    
    /**
     * @Column(type="string")
     * @var string
     */
    private $vars;
    
    /**
     * Return session id
     * @return string
     */
    public function getSessId() {
        return $this->sessId;
    }
    
    /**
     * Return creation date
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }
    
    /**
     * Return last modification date
     * @return \DateTime
     */
    public function getChanged() {
        return $this->changed;
    }
    
    /**
     * Return ip of the session
     * @return string IP
     */
    public function getIp() {
        return $this->ip;
    }
    
    /**
     * Return the variable in json format
     * @return string json parameters
     */
    public function getVars() {
        return $this->vars;
    }
    
    /**
     * Set the session id
     * @param type $sessId
     * @return \RoundcubeSession
     */
    public function setSessId($sessId) {
        $this->sessId = $sessId;
        return $this;
    }
    
    /**
     * set the creation date
     * @param type $created
     * @return \RoundcubeSession
     */
    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }
    
    /**
     * Set the update time
     * @param type $changed
     * @return \RoundcubeSession
     */
    public function setChanged(\DateTime $changed) {
        $this->changed = $changed;
        return $this;
    }
    
    /**
     * Set the session ip
     * @param type $ip
     * @return \RoundcubeSession
     */
    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }
    
    /**
     * Set the variables (json format)
     * @param type $vars
     * @return \RoundcubeSession
     */
    public function setVars($vars) {
        $this->vars = $vars;
        return $this;
    }

}