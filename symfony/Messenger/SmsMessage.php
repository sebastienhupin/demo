<?php

namespace AppBundle\Services\Messenger;

/**
 * Description of SmsMessage
 *
 * @author sebastienhupin
 */
class SmsMessage extends Message {
    /**
     *
     * @var string
     */
    private $smsId;
    
    /**
     * Gets the sms id
     * 
     * @return string
     */
    public function getSmsId() {
        return $this->smsId;
    }
    
    /**
     * Sets the sms id
     * 
     * @param string $smsId
     * @return $this
     */
    public function setSmsId($smsId) {
        $this->smsId = $smsId;
        return $this;
    }
}
