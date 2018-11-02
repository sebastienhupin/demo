<?php

namespace AppBundle\Services\Exporter\Output;

use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Services\Exporter\Document;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Entity\Message\Email;
use AppBundle\Enum\Message\SenderEnum;
use AppBundle\Enum\Message\SendToEnum;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Services\Messenger\Messenger;
use AppBundle\Services\Messenger\Parameters as MessengerParameters;
use Symfony\Component\Serializer\Serializer;
use AppBundle\Services\AccessService;
use AppBundle\Enum\Message\MessageStatusEnum;

/**
 * Description of EmailOutput
 *
 * @author sebastienhupin
 */
class EmailOutput implements OutputInterface {

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
     * @var Serializer
     */
    private $serializer;

    /**
     *
     * @var AccessService 
     */
    private $accessService;
    
    /**
     * The constructor
     * 
     * @param EntityManager $em
     * @param EngineInterface $template
     * @param Messenger $messenger
     * @param Serializer $serializer
     * @param AccessService $accessService
     */
    public function __construct(EntityManager $em, EngineInterface $template, Messenger $messenger, Serializer $serializer, AccessService $accessService) {
        $this->em = $em;
        $this->template = $template;
        $this->messenger = $messenger;
        $this->serializer = $serializer;
        $this->accessService = $accessService;
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function output(DocumentCollection $documents, ExportParameters $exportParameters) {
        $emails = Array();

        foreach ($documents as $document) {
            if (null === $document->getAccess()) {
                continue;
            }

            $email = $this->createEmail($document);

            $this->em->persist($email);
            $emails[] = $email;
        }
        $this->em->flush($emails);
        $this->sendEmails($emails);
    }

    /**
     * Create an email
     * 
     * @param Document $document
     * 
     * @return Email
     */
    private function createEmail(Document $document) {
        $email = new Email();
        $email->setAuthor($this->accessService->getAccess());
        $email->setOrganization($this->accessService->getAccess()->getOrganization());
        $email->setSharingWithAdministration(true);
        $email->setStatus(MessageStatusEnum::READY);
        $email->setSender(SenderEnum::ORGANIZATION);
        $email->setSendTo(SendToEnum::MEMBER_AND_GUARDIANS);
        $email->setRecipientRule(sprintf(self::RULE_PERSON_ID, $document->getAccess()->getId()));
        $email->setAbout('about change me');
        $email->setText("mon text change me");
        if ($document->getFile()) {
            $email->addFile($document->getFile());
        }

        return $email;
    }

    /**
     * Send emails
     * 
     * @param array $emails
     *
     * @throws \Exception
     */
    private function sendEmails(Array $emails) {
        foreach ($emails as $email) {
            $object = $this->serializer->normalize($email, 'json-ld');
            $object["format"] = 'email';

            $parameters = new MessengerParameters();
            $parameters->setAction(MessengerParameters::ACTION_SEND);
            try {
                $this->messenger->setParameters($parameters);
                $this->messenger->dispatch($object);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * 
     *  {@inheritdoc}
     */
    public function supportsOutput(ExportParameters $exportParameters) {
        return self::TYPE === $exportParameters->getOutput();
    }

}
