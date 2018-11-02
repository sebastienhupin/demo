<?php

namespace AppBundle\Services\Messenger\Carrier;

use AppBundle\Enum\Message\SendToEnum;
use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Entity\Core\ContactInterface;
use AppBundle\Services\Contactor\Contactor;

/**
 * Description of AbstractCarrier
 *
 * @author sebastienhupin
 */
class AbstractCarrier {

    /**
     *
     * @var Contactor 
     */
    private $contactor;
    
    /**
     * AbstractCarrier constructor.
     * 
     * @param Contactor $contactor
     */
    public function __construct(Contactor $contactor) {
        $this->contactor = $contactor;  
    }

    /**
     * Get mobile phone
     * 
     * @param ContactInterface $contact
     * @param String sendTo
     * 
     * @return string[]
     */
    public function getMobilephones(ContactInterface $contact, $sendTo = SendToEnum::MEMBER) {        
        $mobilephones = $this->contactor->getMobilephones($contact, $sendTo);        
        return array_unique($mobilephones);
    }

    /**
     * Gets emails
     * 
     * @param ContactInterface $contact
     * @param String sendTo
     * 
     * @return array<email,AppBundle\Entity\Core\ContactInterface>
     */
    public function getEmails(ContactInterface $contact, $sendTo = SendToEnum::MEMBER) {
        return $this->contactor->getEmails($contact, $sendTo);
    }
    
    /**
     * Gets addresses
     * 
     * @param ContactInterface $contact
     * @param string $sendTo
     * 
     * @return array<\AppBundle\Entity\Core\ContactInterface,\AppBundle\Entity\Core\AddressPostal>
     */
    public function getAddresses(ContactInterface $contact, $sendTo = SendToEnum::MEMBER) {
        return $this->contactor->getAddresses($contact, $sendTo);
    }
}
