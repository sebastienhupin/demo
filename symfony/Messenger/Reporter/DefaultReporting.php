<?php

namespace AppBundle\Services\Messenger\Reporter;

use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Message\ReportMessage;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Entity\Message\Message;
use AppBundle\Enum\Message\MessageStatusEnum;
use AppBundle\Entity\AccessAndFunction\Access;
use AppBundle\Enum\Message\ReportMessageSatusEnum;
use AppBundle\Services\Messenger\MessageInterface;

use Swift_Message;

/**
 * Description of DefaultReporting
 *
 * @author sebastienhupin
 */
class DefaultReporting implements ReportingInterface {
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
     *
     * @var array
     */
    private $needFlushed = array();

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
        $originator = $m->getAccess()->getPerson()->getEmail();
        $organization = $m->getAccess()->getOrganization()->getName();

        $report = (object) Array(
            'about' => $m->getAbout(),
            'delivredTo' => Array(),
            'undelivredTo' => Array(),
            'invalid' => Array()
        );

        // Generate a report for each messages send
        foreach($messages as $message) {
            foreach($message->getDelivredTo() as $contact) {
                $report->delivredTo[] = $contact->getContactName();
                $this->createReportMessage($message, $contact, ReportMessageSatusEnum::DELIVERED);
            }

            foreach($message->getUndelivredTo() as $contact) {
                $report->undelivredTo[] = $contact->getContactName();
                $this->createReportMessage($message, $contact, ReportMessageSatusEnum::MISSING);
            }
            
            foreach($message->getInvalidRecipients() as $contact) {
                $report->invalid[] = $contact->getContactName();
                $this->createReportMessage($message, $contact, ReportMessageSatusEnum::MISSING);
            }
        }

        // Flush all needed entities
        $this->em->flush($this->needFlushed);
        
        // Generate the reporting message
        $content = $this->templating->render('message_reporting.html.twig', Array('report' => $report, 'name' => $organization));

        // Send the report to the originator
        $email = Swift_Message::newInstance()
                ->setDescription($m->getAccess()->getOrganization()->getId())
                ->setSubject(sprintf("Rapport d'envoi de votre message : %s",$report->about))
                ->setFrom($originator)
                ->setTo($originator)
                ->setBody($content, 'text/html', 'utf-8');

        $this->mailer->send($email);
    }

    /**
     * Create the reporting message
     * 
     * @param MessageInterface $message
     * @param $contact
     * @param string $status
     */
    protected function createReportMessage(MessageInterface $message, $contact, $status) {
            $messageOriginal = $this->em->find(Message::class, $message->getMessageId());
            // @Todo: Remove the test after the problem with the flush on automatic message are know
            // @see: AppBundle\Services\Messenger\Automatic\Output\DefaultOutput line 83
            if ($messageOriginal->getStatus() !== MessageStatusEnum::SEND) {
                $messageOriginal->setStatus(MessageStatusEnum::SEND);
                $messageOriginal->setDateSent(new \DateTime());
                $this->needFlushed[] = $messageOriginal;
            }

            $reporting = new ReportMessage();
            $reporting->setMessage($messageOriginal);
            $reporting->setStatus($status);
            $reporting->setRecipientType(get_class($contact));
            $reporting->setRecipientId($contact->getId());
            
            $this->em->persist($reporting);
            $this->needFlushed[] = $reporting;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsMessage(Parameters $parameters) {
        return 'send' === $parameters->getAction();
    }
}
