<?php

namespace AppBundle\Services\Messenger;

use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Person\Person;

/**
 * Description of Message
 *
 * @author sebastienhupin
 */
class EmailMessage extends Message {
    /**
     *
     * @var ArrayCollection<Person> 
     */
    private $contactsCC;
    /**
     *
     * @var ArrayCollection<Person>
     */
    private $contactsCci;
    /**
     * @var boolean
     * 
     */    
    private $acknowledgment = false; 
    
    public function __construct() {
        parent::__construct();
        $this->contactsCC = new ArrayCollection();
        $this->contactsCci = new ArrayCollection();
    }
    
    /**
     * Add a contact cc
     * 
     * @param Person $contact
     * @return $this
     */
    public function addContactCC(Person $contact) {
        $this->contactsCC->add($contact);
        return $this;
    }
    
    /**
     * Gets contact cc
     * 
     * @return ArrayCollection<Person>
     */
    public function getContactsCC() {
        return $this->contactsCC;
    }
    
    /**
     * Sets contact cc
     * 
     * @param Array<Person> $contacts
     * @return $this
     */
    public function setContactsCC(Array $contacts) {
        $this->contactsCC = new ArrayCollection($contacts);
        return $this;
    }
    
    /**
     * Add contact cci
     * 
     * @param Person $contact
     * @return $this
     */
    public function addContactCci(Person $contact) {
        $this->contactsCci->add($contact);
        return $this;
    }    
    
    /**
     * Gets contact cci
     * 
     * @return ArrayCollection<Person>
     */
    public function getContactsCci() {
        return $this->contactsCci;
    }
    
    /**
     * Sets contact cci
     * 
     * @param Array<Person> $contacts
     * @return $this
     */
    public function setContactsCci(Array $contacts) {
        $this->contactsCci = new ArrayCollection($contacts);
        return $this;
    }
    
    /**
     * Gets acknowledgment
     * 
     * @return boolean
     */
    public function getAcknowledgment() {
        return $this->acknowledgment;
    }
    
    /**
     * Sets acknowledgment
     * 
     * @param type $acknowledgment
     * @return $this
     */
    public function setAcknowledgment($acknowledgment) {
        $this->acknowledgment = $acknowledgment;
        return $this;
    }

}
