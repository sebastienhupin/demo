<?php

namespace AppBundle\Services\Messenger;

use AppBundle\Entity\AccessAndFunction\Access;
use AppBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Core\ContactInterface;    

/**
 * Description of Message
 *
 * @author sebastienhupin
 */
interface MessageInterface {
    
    /**
     * Gets access
     * 
     * @return Access
     */
    public function getAccess();
    
    /**
     * Sets access
     * 
     * @param Access $access
     * @return $this
     */
    public function setAccess(Access $access);
    
    /**
     * Gets organization
     * 
     * @return Organization
     */
    public function getOrganization();
    
    /**
     * Sets organization
     * 
     * @param Organization $organization
     * @return $this
     */
    public function setOrganization(Organization $organization);
    
    /**
     * Gets the message id
     * 
     * @return int
     */
    public function getMessageId();
    
    /**
     * Sets the message id
     * 
     * @param int $id
     * 
     * @return int
     */
    public function setMessageId($id);
    
    /**
     * Gets name
     * 
     * @return string
     */
    public function getName();
    
    /**
     * Sets name
     * 
     * @param string $name
     * @return $this
     */
    public function setName($name);
    
    /**
     * Gets folder
     * 
     * @return string
     */
    public function getFolder();

    /**
     * Sets folder
     * 
     * @param string $folder
     * @return $this
     */
    public function setFolder($folder);

    /**
     * Gets content
     * 
     * @return string
     */
    public function getContent();
    
    /**
     * Sets content
     * 
     * @param string $content
     * @return $this
     */
    public function setContent($content);
    
    /**
     * Add attachment
     * 
     * @param Object $file
     * @return ArrayCollection
     */
    public function addAttachment($file);
    
    /**
     * Gets attachments
     * 
     * @return ArrayCollection
     */
    public function getAttachments();
    
    /**
     * Sets Attachments
     * 
     * @param Array $files
     * @return $this
     */
    public function setAttachments(Array $files);
    
    /**
     * Add contact
     * 
     * @param ContactInterface $contact
     * @return $this
     */
    public function addContact(ContactInterface $contact);
    
    /**
     * Gets contacts
     * 
     * @return ArrayCollection<ContactInterface>
     */
    public function getContacts();
    
    /**
     * Sets contacts
     * 
     * @param Array<ContactInterface> $contacts
     * @return $this
     */
    public function setContacts(Array $contacts);
    
    /**
     * Gets originator
     * 
     * @return string
     */
    public function getOriginator();
    
    /**
     * Sets originator
     * 
     * @param string $originator
     * @return $this
     */
    public function setOriginator($originator);
    
    /**
     * Gets about
     * 
     * @return string
     */
    public function getAbout();
    
    /**
     * Sets about
     * 
     * @param string $about
     * @return $this
     */
    public function setAbout($about);
    
    /**
     * Add person to the delivered to
     * 
     * @param ContactInterface $contact
     */
    public function addDelivredTo(ContactInterface $contact);
    
    /**
     * Gets delivered to
     * 
     * @return ArrayCollection<Access>
     */    
    public function getDelivredTo();
    
    /**
     * Set delivered to
     * 
     * @param Array<ContactInterface> $delivredTo
     * @return $this
     */    
    public function setDelivredTo(Array $delivredTo);
    
    /**
     * Add person to the undelivered to
     * 
     * @param $contact
     */
    public function addUndelivredTo(ContactInterface $contact);
    
    /**
     * Gets undelivered to
     * 
     * @return ArrayCollection<ContactInterface>
     */
    public function getUndelivredTo();
    
    /**
     * Sets undelivered to
     * 
     * @param Array<ContactInterface> $undelivredTo
     * @return $this
     */
    public function setUndelivredTo(Array $undelivredTo);
    
    /**
     * Add recipient
     * 
     * @param string $recipient
     * 
     * @return $this
     */
    public function addRecipient($recipient);
    
    /**
     * Add recipients
     * 
     * @param array $recipients
     * 
     * @return $this
     */
    public function addRecipients(Array $recipients);    
    
    /**
     * Gets recipients
     * 
     * @return ArrayCollection
     */
    public function getRecipients();
    
    /**
     * Sets recipients
     * 
     * @param array $recipient
     */
    public function setRecipients(Array $recipient);

    /**
     * Gets status
     */
    public function getStatus();
    
    /**
     * Sets status
     * 
     * @param type $status
     * 
     * @return $this
     */
    public function setStatus($status);
    
    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage();
    
    /**
     * Set error message
     * 
     * @param string $message
     * 
     * @return $this
     */
    public function setErrorMessage($message);
    
    /**
     * Get invalid recipients
     * 
     * @return ArrayCollection
     */
    public function getInvalidRecipients();
    
    /**
     * add invalid recipients
     * 
     * @return ArrayCollection
     */
    public function addInvalidRecipient($recipient);
    
    /**
     * Set invalid $recipients
     * 
     * @param array $recipients
     * 
     * @return $this
     */
    public function setInvalidRecipients($recipients);
}
