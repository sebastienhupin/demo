<?php

namespace AppBundle\Services\Messenger;

use AppBundle\Entity\AccessAndFunction\Access;
use AppBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Core\File;
use Doctrine\DBAL\Types\JsonArrayType;
use AppBundle\Entity\Core\ContactInterface;


/**
 * Description of Message
 *
 * @author sebastienhupin
 */
class Message implements MessageInterface {
    /**
     *
     * @var Access 
     */
    protected $access;
    /**
     *
     * @var Organization 
     */
    protected $organization;    
    /**
     *
     * @var int 
     */
    protected $messageId;
    /**
     *
     * @var string 
     */
    protected $name;
    /**
     *
     * @var string 
     */
    protected $folder;
    /**
     *
     * @var string
     */
    protected $originator;
    /**
     *
     * @var ArrayCollection<ContactInterface>
     */
    protected $contacts;
    /**
     *
     * @var JsonArrayType
     */
    protected $who;
    /**
     *
     * @var string 
     */
    protected $about;
    /**
     *
     * @var string 
     */
    protected $content;
    /**
     *
     * @var ArrayCollection 
     */
    protected $attachments;    
    /**
     *
     * @var ArrayCollection 
     */
    protected $delivredTo;    
    /**
     *
     * @var ArrayCollection 
     */
    protected $undelivredTo;
    /**
     *
     * @var ArrayCollection 
     */
    protected $recipients;
    /**
     *
     * @var string 
     */
    protected $status;
    /**
     *
     * @var string
     */
    protected $errorMessage;
    
    /**
     * @var ArrayCollection
     */
    protected $invalidRecipients;
    /**
     *
     * @var File 
     */
    protected $file;
    /**
     * The constructor
     */
    public function __construct() {
        $this->attachments = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->delivredTo = new ArrayCollection();
        $this->undelivredTo = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->invalidRecipients = new ArrayCollection();
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getAccess() {
        return $this->access;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setAccess(Access $access) {
        $this->access = $access;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getOrganization() {
        return $this->organization;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setOrganization(Organization $organization) {
        $this->organization = $organization;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */    
    public function getMessageId() {
        return $this->messageId;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setMessageId($id) {
        $this->messageId = $id;
    }    
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getFolder() {
        return $this->folder;
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function setFolder($folder) {
        $this->folder = $folder;
        return $this;
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function getContent() {
        return $this->content;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function addAttachment($file) {
        $this->attachments->add($file);
        return $this->attachments;
    }    
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getAttachments() {
        return $this->attachments;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setAttachments(Array $files) {
        $this->attachments = new ArrayCollection ($files);
        return $this;
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function addContact(ContactInterface $contact) {
        $this->contacts->add($contact);
        return $this;
    }    
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getContacts() {
        return $this->contacts;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setContacts(Array $contacts) {
        $this->contacts = new ArrayCollection ($contacts);
        return $this;
    }
    /**
     *
     * {@inheritdoc}
     */
    public function getWho() {
        return $this->who;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function setWho($who) {
        $this->who = $who;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getOriginator() {
        return $this->originator;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setOriginator($originator) {
        $this->originator = $originator;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getAbout() {
        return $this->about;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setAbout($about) {
        $this->about = $about;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function addDelivredTo(ContactInterface $contact) {
        $this->delivredTo->add($contact);
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getDelivredTo() {
        return $this->delivredTo;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function setDelivredTo(Array $delivredTo) {
        $this->delivredTo = new ArrayCollection($delivredTo);
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function addUndelivredTo(ContactInterface $contact) {
        $this->undelivredTo->add($contact);
        
        return $this;
    }    
    
    /**
     * 
     * {@inheritdoc}
     */
    public function getUndelivredTo() {
        return $this->undelivredTo;
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function setUndelivredTo(Array $undelivredTo) {
        $this->undelivredTo = new ArrayCollection($undelivredTo);
        return $this;
    }

    /**
     * 
     * {@inheritdoc}
     */    
    public function addRecipient($recipient) {
        $this->recipients->add($recipient);
    }

    /**
     * 
     * {@inheritdoc}
     */    
    public function addRecipients(array $recipients) {
        $recipients = array_merge($this->recipients->toArray(), $recipients);
        $this->recipients = new ArrayCollection($recipients);
    }    
    
    /**
     * 
     * {@inheritdoc}
     */    
    public function getRecipients() {
        return $this->recipients;
    }

    /**
     * 
     * {@inheritdoc}
     */    
    public function setRecipients(array $recipient) {
        $this->recipients = new ArrayCollection($recipient);
    }
    
    /**
     * 
     * {@inheritdoc}
     */      
    public function getStatus() {
        return $this->status;
    }

    /**
     * 
     * {@inheritdoc}
     */      
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    
    /**
     * 
     * {@inheritdoc}
     */    
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    /**
     * 
     * {@inheritdoc}
     */    
    public function setErrorMessage($message) {
        $this->errorMessage = $message;
    }

    /**
     * 
     * {@inheritdoc}
     */      
    public function addInvalidRecipient($recipient) {
        $this->invalidRecipients->add($recipient);
    }

    /**
     * 
     * {@inheritdoc}
     */      
    public function getInvalidRecipients() {
        return $this->invalidRecipients;
    }

    /**
     * 
     * {@inheritdoc}
     */      
    public function setInvalidRecipients($recipients) {
        $this->invalidRecipients = new ArrayCollection($recipients);

        return $this;
    }
    
    /**
     * Gets the file
     * 
     * @return File
     */
    public function getFile() {
        return $this->file;
    }
    
    /**
     * Sets the file
     * 
     * @param File $file
     * @return $this
     */
    public function setFile(File $file) {
        $this->file = $file;
        return $this;
    }
}
