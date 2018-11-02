<?php
namespace AppBundle\Services\Messenger;


/**
 * Description of Parameters
 *
 * @author sebastienhupin
 */
class Parameters {

    const ACTION_SEND = 'send';
    const ACTION_PRINT = 'print';
    const ACTION_PREVIEW = 'preview';
    const ACTION_CHECK = 'check';
    
    /**
     *
     * @var integer
     */
    private $organizationId;    
    /**
     *
     * @var integer  
     */
    private $accessId;    
    /**
     *
     * @var Array 
     */
    private $data;
    /**
     *
     * @var string 
     */
    private $action;
    
    /**
     * Gets organization id.
     * 
     * @return integer
     */
    public function getOrganizationId() {
        return $this->organizationId;
    }
    
    /**
     * Sets organization id.
     * 
     * @param integer $organizationId
     * @return $this
     */
    public function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
        return $this;
    }
        
    /**
     * Gets access id.
     * 
     * @return int
     */
    public function getAccessId() {
        return $this->accessId;
    }
    
    /**
     * Sets access id.
     * 
     * @param int $id
     * @return $this
     */
    public function setAccessId($id) {
        $this->accessId = $id;
        return $this;
    }
    
    /**
     * Gets data
     * 
     * @return Array
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Sets data
     * @param array $data
     * @return $this
     */
    public function setData(Array $data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Gets action
     * 
     * @return string
     */
    public function getAction() {
        return $this->action;
    }
    
    /**
     * Sets action
     * 
     * @param string $action
     * @return $this
     */
    public function setAction($action) {
        if (!in_array($action, array(self::ACTION_PREVIEW, self::ACTION_PRINT, self::ACTION_SEND, self::ACTION_CHECK))) {
            throw new \Exception(sprintf('action should be one of this %s', implode(',', array(self::ACTION_PREVIEW, self::ACTION_PRINT, self::ACTION_SEND, self::ACTION_CHECK))));
        }
        $this->action = $action;
        return $this;
    }

}
