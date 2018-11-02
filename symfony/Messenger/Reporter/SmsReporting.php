<?php

namespace AppBundle\Services\Messenger\Reporter;

use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Message\ReportMessage;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Entity\Message\Message;
use AppBundle\Enum\Message\ReportMessageSatusEnum;
use AppBundle\Enum\Message\MessageStatusEnum;
use AppBundle\Entity\AccessAndFunction\Access;

use Swift_Message;

/**
 * Description of SmsReporting
 *
 * @author sebastienhupin
 */
class SmsReporting implements ReportingInterface {
    /**
     *
     * @var EntityManagerInterface 
     */
    private $em;
    /**
     *
     * @var Object 
     */
    private $mailer;
    /**
     *
     * @var EngineInterface 
     */
    private $templating;
    
    /**
     * The constructor
     * 
     * @param EntityManagerInterface $em
     * @param Object $mailer
     */
    public function __construct(EntityManagerInterface $em, $mailer, EngineInterface $templating) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }
    
    /**
     * {@inheritdoc}
     */
    public function reporting(MessageCollection $messages, Parameters $parameters) {
        
        $m = $messages->first();
        $organization = $m->getAccess()->getOrganization()->getName();
        $originator = $m->getAccess()->getPerson()->getEmail();

        $report = (object) Array(
            'about' => $m->getAbout(),
            'delivredTo' => Array(),
            'undelivredTo' => Array(),
            'invalid' => Array()
        );

        $messageOriginal = $this->em->find(Message::class, $m->getMessageId());
        $messageOriginal->setStatus(MessageStatusEnum::SEND);
        $messageOriginal->setDateSent(new \DateTime());
        $this->em->persist($messageOriginal);

        // Generate a report for each messages send
        foreach($messages as $message) {
            foreach($message->getDelivredTo() as $contact) {
                $report->delivredTo[] = $contact->getPerson()->getFullname();
                $this->createReportMessage($messageOriginal, $contact, ReportMessageSatusEnum::DELIVERY_IN_PROGRESS, $message->getSmsId());
            }

            foreach($message->getUndelivredTo() as $contact) {
                $report->undelivredTo[] = $contact->getPerson()->getFullname();
                $this->createReportMessage($messageOriginal, $contact, ReportMessageSatusEnum::MISSING, $message->getSmsId());
            }

            foreach($message->getInvalidRecipients() as $contact) {
                $report->invalid[] = $contact->getPerson()->getFullname();
                $this->createReportMessage($messageOriginal, $contact, ReportMessageSatusEnum::INVALID, $message->getSmsId());
            } 
        }

        $this->em->flush();

        // Generate the reporting message
        $content = $this->templating->render('message_reporting.html.twig', Array('report' => $report, 'name' => $organization));

        // Send the report to the originator
        $email = Swift_Message::newInstance()
                ->setDescription($message->getOrganization()->getId())
                ->setSubject(sprintf("Rapport d'envoi de votre message : %s",$report->about))
                ->setFrom($originator)
                ->setTo($originator)
                ->setBody($content);

        $this->mailer->send($email);
    }
    
    /**
     * Create the reporting message
     * 
     * @param Message $message
     * @param Access $contact
     * @param string $status
     * @param string $smsId
     */
    protected function createReportMessage(Message $message, Access $contact, $status, $smsId) {
            $reporting = new ReportMessage();
            $reporting->setMessage($message);
            $reporting->setStatus($status);
            $reporting->setRecipient($contact);
            $reporting->setSmsId($smsId);
            $this->em->persist($reporting);
    }    
    
    /**
     * {@inheritdoc}
     */
    public function supportsMessage(Parameters $parameters) {
        return 'sms' === $parameters->getData()['format'] && 'send' === $parameters->getAction();
    }
}
