<?php

namespace AppBundle\Services\Messenger\Normalizer;

use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\Message;

/**
 * Description of MailNormalizer
 *
 * @author sebastienhupin
 */
class MailNormalizer extends AbstractNormalizer implements NormalizerInterface {
    /**
     * 
     * {@inheritdoc}
     */
    public function normalize(\AppBundle\Services\Messenger\Parameters $parameters) {
        $messages = new MessageCollection();
        $data = $parameters->getData();

        $contacts = $this->getContactsFromRule($data['recipientRule']);
        
        if (empty($contacts)) {
            throw new \Exception("No recipents found");
        }

        $text = $this->cleanTemplate($data['text']);

        $id = uniqid();
        $template_tmp = fopen(sprintf(__DIR__."/template_tmp/%s.html.twig", $id), "w+");
        fwrite($template_tmp,$text);
        fclose($template_tmp);

        foreach($contacts as $contact) {
            $message = new Message();
            $message->setAbout($data['about']);
            $message->setMessageId($this->getIdFromIri($data['@id']));
            $message->setOrganization($this->getAccess()->getOrganization());
            $message->setAccess($this->getAccess());
            $message->addContact($contact);
            $message->setContent($this->generateContent($contact, $id));
            $messages->add($message);
        }
        @unlink((sprintf(__DIR__."/template_tmp/%s.html.twig", $id)));
        return $messages;        
    }
    /**
     * 
     * {@inheritdoc}
     */
    public function supportsNormalization(\AppBundle\Services\Messenger\Parameters $parameters) {
         return 'mail' === $parameters->getData()['format'];
    }

}
