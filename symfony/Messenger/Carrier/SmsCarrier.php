<?php

namespace AppBundle\Services\Messenger\Carrier;

use AppBundle\Sms\SenderInterface;
use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Enum\Message\ReportMessageSatusEnum;
use SmsSender\Result\ResultInterface;
use AppBundle\Services\Contactor\Contactor;
/**
 * Description of SmsCarrier
 *
 * @author sebastienhupin
 */
class SmsCarrier extends AbstractCarrier implements CarrierInterface {    
    /**
     *
     * @var SenderInterface 
     */
    private $sender;
    
    /**
     * The constructor
     * 
     * @param Contactor $contactor
     * @param SenderInterface $sender
     */
    public function __construct(Contactor $contactor, SenderInterface $sender) {
        parent::__construct($contactor);
        $this->sender = $sender;
    }

    public function connect($login, $pass){
        return $this->sender->credit('n', $login, $pass);
    }
    
    /**
     * 
     * {@inheritdoc}
     */    
    public function send(MessageCollection $messages, Parameters $parameters, $delivery = true) {
        $data = $parameters->getData();
        $sendTo = $data['sendTo'];
        
        foreach($messages as $message) {            
            foreach($message->getContacts() as $contact) {
               $mobilephones = $this->getMobilephones($contact, $sendTo);
               if (empty($mobilephones)) {
                   $message->addUndelivredTo($contact);
               }
               else {
                   $message->addDelivredTo($contact);
                   $message->addRecipients($mobilephones);
               }
            }
            
            if (0 === $message->getRecipients()->count()) {
                $message->setStatus(ReportMessageSatusEnum::MISSING);
                $message->setErrorMessage('No recipients found');
                continue;
            }

            $status = $this->getStatus('sent');
            
            if ($delivery) {
                $result = $this->sender->send($message->getRecipients(), $message->getContent(), $message->getOriginator());
                if (isset($result['status'])) {
                    $status = $this->getStatus($result['status']);
                    $message->setSmsId($result['id']);
                }
                else {
                    $status = $this->getStatus(ResultInterface::STATUS_FAILED);
                }
            }

            $message->setStatus($status);
        }
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function check(\AppBundle\Services\Messenger\MessageCollection $messages, \AppBundle\Services\Messenger\Parameters $parameters) {
        $response = $this->sender->credit('n');
     
        if (array_key_exists('status', $response) && 'failed' === $response['status']) {
            throw new \Exception('An error is occured when checking sms credits');
        }
 
        // Debug only
//        $response = array(
//            'monetary' => 'euro',
//            'credit' => 1,
//            'type' => 'credit'
//        );

        $data = $parameters->getData();
        $sendTo = $data['sendTo'];
        $totalMessage = 0;

        foreach($messages as $message) {
            foreach($message->getContacts() as $contact) {
                $mobilephones = $this->getMobilephones($contact, $sendTo);
                $totalMessage+=count($mobilephones);
            }            
        }

        $totalCredit = $response['credit'];
        
        $m = $messages->first();
        $text = $m->getContent();
        $totalCreditAfterSend = $totalCredit - $totalMessage;
        if (0>$totalCreditAfterSend) {
            throw new PaymentRequiredException('not_enough_credit', 402, null, Array(
                'NUMBER_CREDIT_TO_SEND_YOUR_SMS' => $totalMessage,
                'CREDIT_REMAINING' => $totalCreditAfterSend
            ));
        }
        else {
            return Array(
                'TEXT' => $text,
                'NUMBER_CREDIT_TO_SEND_YOUR_SMS' => $totalMessage,
                'CREDIT_REMAINING' => $totalCreditAfterSend
            );
        }
    }    
    
    /**
     * 
     * @param string $status
     * @return string
     */
    private function getStatus($status) {
        
        switch ($status) {
          case ResultInterface::STATUS_SENT:
          case ResultInterface::STATUS_DELIVERED:
          case ResultInterface::STATUS_QUEUED:    
            return ReportMessageSatusEnum::DELIVERED;          
          case ResultInterface::STATUS_FAILED;
            return ReportMessageSatusEnum::INVALID;
        }       
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function supportsMessage(Parameters $parameters) {
        return 'sms' === $parameters->getData()['format'] && ('send' === $parameters->getAction() || 'check' === $parameters->getAction());
    }

}
