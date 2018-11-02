<?php

namespace AppBundle\Services\Messenger\Automatic\Output;

use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\Message;
use AppBundle\Model\AutomaticMessenger\Parameters;
use AppBundle\Entity\Message\Email;
use AppBundle\Enum\Message\SenderEnum;
use AppBundle\Enum\Message\SendToEnum;
use Doctrine\ORM\EntityManager;
use AppBundle\Services\Messenger\Messenger;
use AppBundle\Services\Messenger\Parameters as MessengerParameters;
use AppBundle\Enum\Message\MessageStatusEnum;

/**
 * Description of DefaultOutput
 *
 * @author sebastienhupin
 */
class DefaultOutput implements OutputInterface {

    const TYPE = 'email';
    const RULE_PERSON_ID = "person.id = %d";

    /**
     *
     * @var EntityManager 
     */
    private $em;
    /**
     *
     * @var EngineInterface 
     */
    private $template;
    /**
     *
     * @var Messenger 
     */
    private $messenger;
    
    /**
     * The constructor
     * 
     * @param EntityManager $em
     * @param Messenger $messenger
     */
    public function __construct(EntityManager $em, Messenger $messenger) {
        $this->em = $em;
        $this->messenger = $messenger;
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function output(MessageCollection $messages, Parameters $parameters) {
        
        foreach ($messages as $message) {
            if (null === $message->getAccess()) {
                continue;
            }

            $email = $this->createEmail($message);
            $message->setMessageId($email->getId());
        }
        
        $this->sendEmails($messages);
    }

    /**
     * Create an email
     * 
     * @param Document $message
     * 
     * @return Email
     */
    private function createEmail(Message $message) {
        $email = new Email();
        $email->setAuthor($message->getAccess());
        $email->setOrganization($message->getOrganization());
        $email->setSharingWithAdministration(true);
        // @Todo: review, normally the status and date sent are set in de reporting
        $email->setStatus(MessageStatusEnum::SEND);
        $email->setDateSent(new \DateTime());
        
        $email->setSender(SenderEnum::ORGANIZATION);
        $email->setSendTo(SendToEnum::MEMBER_AND_GUARDIANS);
        $email->setRecipientRule($this->createRule($message));
        $email->setAbout($message->getAbout());
        $email->setText($message->getContent());
        
        $this->em->persist($email);
        $this->em->flush($email);
        
        return $email;
    }

    private function createRule(Message $message) {
        $rules = Array();
        
        foreach($message->getContacts() as $contact) {
           $rules[] =  sprintf(self::RULE_PERSON_ID, $contact->getId());
        }

        return implode(' OR ', $rules);
    }

    /**
     * Send emails
     * 
     * @param MessageCollection $messages
     *
     * @throws \Exception
     */
    private function sendEmails(MessageCollection $messages) {

        $parameters = new MessengerParameters();
        $parameters->setAction(MessengerParameters::ACTION_SEND);
        $parameters->setData(array(
            'format' => 'email',
            'sendTo' => SendToEnum::MEMBER_AND_GUARDIANS
        ));
        
        try {
            $this->messenger->setParameters($parameters);
            $this->messenger->send($messages);        
            $this->messenger->reporting($messages);
            $this->messenger->notify($messages);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsOutput(Parameters $parameters) {
        return true;
    }

}
