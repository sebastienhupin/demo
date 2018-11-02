<?php

namespace AppBundle\Services\Messenger\Normalizer;

use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\SmsMessage;

/**
 * Description of SmsNormalizer
 *
 * @author sebastienhupin
 */
class SmsNormalizer extends AbstractNormalizer implements NormalizerInterface {
    /**
     * 
     * {@inheritdoc}
     */
    public function normalize(\AppBundle\Services\Messenger\Parameters $parameters) {
        $messages = new MessageCollection();
        $data = $parameters->getData();
        
        $originator = $this->getAccess()->getOrganization()->getParameters()->getSmsSenderName();
        
        $contacts = $this->getContactsFromRule($data['recipientRule']);
        
        if (empty($contacts)) {
            throw new \Exception("No recipents found");
        }        

        $message = new SmsMessage();
        $message->setMessageId($this->getIdFromIri($data['@id']));
        $message->setOriginator($originator);
        $message->setOrganization($this->getAccess()->getOrganization());
        $message->setAccess($this->getAccess());
        $message->setContent($data['text']);        
        
        foreach($contacts as $contact) {
            $message->addContact($contact);            
        }
        
        $messages->add($message);
        
        return $messages;
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Services\Messenger\Parameters $parameters) {
         return 'sms' === $parameters->getData()['format'];
    }

}
