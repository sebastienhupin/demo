<?php

namespace AppBundle\Services\Messenger\Carrier;

use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Enum\Message\ReportMessageSatusEnum;
use AppBundle\Services\File\FileManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Swift_Attachment;
use Swift_Message;
use AppBundle\Services\Contactor\Contactor;

/**
 * Description of EmailCarrier
 *
 * @author sebastienhupin
 */
class EmailCarrier extends AbstractCarrier implements CarrierInterface {
    /**
     *
     * @var Object 
     */
    private $mailer;
    /**
     *
     * @var FileManager 
     */
    private $fileManager;
    private $transport;

    /**
     * The constructor
     * 
     * @param Contactor $contactor
     * @param Object $mailer
     * @param FileManager $fileManager
     * @param type $transport
     */
    public function __construct(Contactor $contactor, $mailer, FileManager $fileManager, $transport) {
        parent::__construct($contactor);

        $this->mailer = $mailer;
        $this->fileManager = $fileManager;
        $this->transport = $transport;
    }
    
    /**
     * 
     * {@inheritdoc}
     */  
    public function send(MessageCollection $messages, Parameters $parameters, $delivery = true) {
        $data = $parameters->getData();
        $sendTo = $data['sendTo'];

        foreach($messages as $message) {
            
            $delivredTo = Array();            
            foreach($message->getContacts() as $contact) {
                $emails = $this->getEmails($contact, $sendTo);
                if (empty($emails)) {
                    $message->addUndelivredTo($contact);
                }
                else {
                  $delivredTo = array_merge($delivredTo, $emails);
                }
            }
            
            $delivredCc = Array();            
            foreach($message->getContactsCc() as $contact) {
                $emails = $this->getEmails($contact, $sendTo);
                if (empty($emails)) {
                    $message->addUndelivredTo($contact);
                }
                else {
                  $delivredCc = array_merge($delivredTo, $emails);
                }
            }            
            
            $delivredCci = Array();            
            foreach($message->getContactsCci() as $contact) {
                $emails = $this->getEmails($contact, $sendTo);
                if (empty($emails)) {
                    $message->addUndelivredTo($contact);
                }
                else {
                  $delivredCci = array_merge($delivredCci, $emails);
                }
            }

            // If there is no contacts with a valid email, set the status message to error
            if (empty($delivredTo) && empty($delivredCc) && empty($delivredCci)) {
                $message->setStatus(ReportMessageSatusEnum::MISSING);
                $message->setErrorMessage('No recipients found');
                continue;
            }

            $email = Swift_Message::newInstance()
                    ->setDescription($message->getOrganization()->getId())
                    ->setSubject($message->getAbout())
                    ->setFrom($message->getOriginator())
                    ->setBody($message->getContent(), 'text/html', 'utf-8');
            
            if ($message->getAcknowledgment()) {
                $email->setReadReceiptTo($message->getOriginator());
            }
            
            // Add each files
            foreach ($message->getAttachments() as $file) {
                $this->fileManager->init($file);
                $content = $this->fileManager->get($file->getSlug());
                $attachment = Swift_Attachment::newInstance($content, $file->getName(), $file->getMimeType());

                $email->attach($attachment);    
            }
            
            // Add each recipients separatly   
            foreach($delivredTo as $address) {
                try {
                    $email->addTo($address['email'], $address['contact']->getContactName());
                    $message->addDelivredTo($address['contact']);
                    $message->addRecipient($address['email']);                     
                }
                catch(\Swift_RfcComplianceException $e) {
                    $message->addInvalidRecipient($address['email']);
                }                   
            }

            // Add each recipients cc   
            foreach($delivredCc as $address) {
                try {
                    $email->addCc($address['email'], $address['contact']->getContactName());
                    $message->addDelivredTo($address['contact']);
                    $message->addRecipient($address['email']);
                }
                catch(\Swift_RfcComplianceException $e) {
                    $message->addInvalidRecipient($address['email']);
                }                   
            }            
            
            // Add each recipients cci   
            foreach($delivredCci as $address) {
                try {    
                    $email->addBcc($address['email'], $address['contact']->getContactName());
                    $message->addDelivredTo($address['contact']);
                    $message->addRecipient($address['email']);
                }
                catch(\Swift_RfcComplianceException $e) {
                    $message->addInvalidRecipient($address['email']);
                }                   
            }            

            try {
                $result = $this->mailer->send($email);

                $spool = $this->mailer->getTransport()->getSpool();
                $spool->flushQueue($this->transport);
            }
            catch(\Exception $e) {                
                throw $e;
            }
            
            $message->setStatus(ReportMessageSatusEnum::DELIVERED);          
        }
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function check(\AppBundle\Services\Messenger\MessageCollection $messages, \AppBundle\Services\Messenger\Parameters $parameters) {
        
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function supportsMessage(\AppBundle\Services\Messenger\Parameters $parameters) {
        return 'email' === $parameters->getData()['format'] && ('send' === $parameters->getAction() || 'check' === $parameters->getAction());
    }

}
