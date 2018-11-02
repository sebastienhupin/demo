<?php

namespace AppBundle\Services\Messenger\Carrier;

use AppBundle\Enum\Message\ReportMessageSatusEnum;
use AppBundle\Services\Contactor\Contactor;

/**
 * Description of MailCarrier
 *
 * @author sebastienhupin
 */
class MailCarrier extends AbstractCarrier implements CarrierInterface {
     
    /**
     * 
     * {@inheritdoc}
     */    
    public function send(\AppBundle\Services\Messenger\MessageCollection $messages, \AppBundle\Services\Messenger\Parameters $parameters, $delivery = true) {
        $data = $parameters->getData();
        $sendTo = $data['sendTo'];

        foreach($messages as $message) {            
            foreach($message->getContacts() as $contact) {
               $addresses = $this->getAddresses($contact, $sendTo);
               if (empty($addresses)) {
                   $message->addUndelivredTo($contact);
               }
               else {
                   $message->addDelivredTo($contact);
                   $message->addRecipients($addresses);
               }
            }

            if (0 === $message->getRecipients()->count()) {
                $message->setStatus(ReportMessageSatusEnum::MISSING);
                $message->setErrorMessage('No recipients found');
                continue;
            }

            if ($delivery) {
                // Generate the document
                
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
        return 'mail' === $parameters->getData()['format'] && ('send' === $parameters->getAction() || 'check' === $parameters->getAction());
    }

}
