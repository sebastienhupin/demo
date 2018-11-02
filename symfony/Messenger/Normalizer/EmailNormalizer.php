<?php

namespace AppBundle\Services\Messenger\Normalizer;

use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\EmailMessage;
use AppBundle\Enum\Message\SenderEnum;
use AppBundle\Entity\Core\File;
use AppBundle\Entity\AccessAndFunction\Access;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Enum\Core\ContactPointTypeEnum;

/**
 * Description of EmailNormalizer
 *
 * @author sebastienhupin
 */
class EmailNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    /**
     *
     * {@inheritdoc}
     */
    public function normalize(\AppBundle\Services\Messenger\Parameters $parameters)
    {
        $messages = new MessageCollection();
        $data = $parameters->getData();

        $contacts = $this->getContactsFromRule($data['recipientRule']);
        $contactsCc = $this->getContactsFromRule($data['recipientCcRule']);
        $contactsCci = $this->getContactsFromRule($data['recipientCciRule']);

        $originator = $data['sender'];

        if (SenderEnum::ORGANIZATION === $data['sender']) {
            if ($this->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL)) {
                $originator = $this->getAccess()->getOrganization()->getContactPointForType(ContactPointTypeEnum::PRINCIPAL)->getEmail();
            } else {
                $originator = $this->getAccess()->getPerson()->getEmail();
            }
        }

        if (empty($originator)) {
            throw new \Exception("No originator found");
        }

        if (empty($contacts) && empty($contactsCc) && empty($contactsCci)) {
            throw new \Exception("No recipents found");
        }

        $attachments = Array();

        foreach ($data['files'] as $d) {
            $attachments[] = $this->getFileFromData($d);
        }

        $text = $this->cleanTemplate($data['text']);

        // If there is some people in the Cc or Cci, the message is uniq for all people
        /*   if (!empty($contactsCc) || !empty($contactsCci)) {
               $message = new EmailMessage();
               $message->setAbout($data['about']);
               $message->setOriginator($originator);
               $message->setMessageId($this->getIdFromIri($data['@id']));
               $message->setOrganization($this->getAccess()->getOrganization());
               $message->setAccess($this->getAccess());
               $message->setContent($text);
               $message->setAcknowledgment($data['acknowledgment']);

               foreach($people as $contact) {
                   $message->addContact($contact);
                   foreach($data['documents'] as $document) {
                        $documents = $this->generateDocument($document, $contact);
                        foreach ($documents as $doc) {
                            $attachments[] = $doc->getFile();
                        }
                   }
               }

               foreach($contactsCc as $contact) {
                   $message->addContactCC($contact);
               }
               foreach($contactsCci as $contact) {
                   $message->addContactCci($contact);
               }
               $message->setAttachments($attachments);
               $messages->add($message);
           }
           else {
        */
        $id = uniqid();
        $template_tmp = fopen(sprintf(__DIR__ . "/template_tmp/%s.html.twig", $id), "w+");
        fwrite($template_tmp, $text);
        fclose($template_tmp);

        foreach ($contacts as $contact) {
            $message = new EmailMessage();
            $message->setAbout($data['about']);
            $message->setOriginator($originator);
            $message->setMessageId($this->getIdFromIri($data['@id']));
            $message->setOrganization($this->getAccess()->getOrganization());
            $message->setAccess($this->getAccess());
            $message->addContact($contact);
            $message->setContent($this->generateContent($contact, $id));
            $message->setAcknowledgment($data['acknowledgment']);

            foreach ($data['documents'] as $document) {
                $documents = $this->generateDocument($document, $contact);
                foreach ($documents as $doc) {
                    $attachments[] = $doc->getFile();
                }
            }

            $message->setAttachments($attachments);

            $messages->add($message);
        }

        @unlink((sprintf(__DIR__ . "/template_tmp/%s.html.twig", $id)));

        return $messages;
    }

    /**
     * Get denormalize file data to File object
     *
     * @param array $data
     *
     * @return File
     */
    private function getFileFromData(Array $data)
    {
        return $this->serializer->denormalize($data, File::class, 'json-ld', Array('resource' => $this->resourceCollection->getResourceForEntity(File::class)));
    }

    /**
     * Generate document
     *
     * @param array $document
     * @param Access $access
     *
     * @return \AppBundle\Services\Exporter\DocumentCollection
     * @throws \Exception
     */
    private function generateDocument(Array $document, Access $access)
    {
        $document['text'] = $this->generateContent($access, $document['text']);
        $exportParameters = new ExportParameters();
        $exportParameters->setName(sprintf("%s_%d", $document['name'], $access->getId()));
        $exportParameters->setView('document');
        $exportParameters->setFormat('pdf');
        $this->exporter->setExportParameters($exportParameters);
        try {
            $documents = $this->exporter->directExport($document);
        } catch (\Exception $e) {
            throw $e;
        }

        return $documents;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Services\Messenger\Parameters $parameters)
    {
        return 'email' === $parameters->getData()['format'];
    }
}
