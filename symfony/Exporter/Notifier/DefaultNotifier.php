<?php

namespace AppBundle\Services\Exporter\Notifier;

use AppBundle\Services\AccessService;
use AppBundle\Services\Exporter\ErrorCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use AppBundle\Event\Core\NotificationEvent;
use AppBundle\Entity\Core\Notification;
use AppBundle\Enum\Core\NotificationTypeEnum;
use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\DocumentCollection;

/**
 * Description of DefaultNotifier
 *
 * @author sebastienhupin
 */
class DefaultNotifier implements NotifierInterface {
    /**
     *
     * @var UrlGeneratorInterface 
     */
    private $urlGenerator;    
    /**
     *
     * @var EventDispatcherInterface 
     */
    private $dispatcher;
    /**
     * @var AccessService
     */
    private $accessService;
    /**
     * The constructor
     * 
     * @param UrlGeneratorInterface $urlGenerator
     * @param EventDispatcherInterface $dispatcher     
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EventDispatcherInterface $dispatcher = null, AccessService $accessService)
    {
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher = $dispatcher;
        $this->accessService = $accessService;
    }

    /**
     * 
     *  {@inheritdoc}
     */ 
    public function notify(DocumentCollection $documents, ExportParameters $exportParameters = null, ErrorCollection $errors) {
        
        foreach($documents as $document) {
            $link = $this->urlGenerator->generate('opentalent_file_donwload', array('id' => $document->getFile()->getId()));
            // Send a notification event
            $notification = new Notification();
            $notification->setName('export')
                    ->setRecipientAccess($document->getAccess())
                    ->setRecipientOrganization($document->getOrganization())
                    ->setMessage(['fileName' => $document->getFile()->getName()] )
                    ->setLink($link)
                    ->setType(NotificationTypeEnum::FILE);
            
            $notificationType = null;
            $notificationEvent = new NotificationEvent($notification, $notificationType);

            $this->dispatcher->dispatch(NotificationEvent::NAME, $notificationEvent);
        }

        /**
         * Errors notifications
         */
        if($errors){
            foreach($errors as $error) {
                // Send a notification event
                $notification = new Notification();
                $notification->setName($error['name'])
                    ->setRecipientAccess($this->accessService->getAccess())
                    ->setRecipientOrganization($this->accessService->getOrganization())
                    ->setMessage($error['message'])
                    ->setType(NotificationTypeEnum::ERROR);

                $notificationType = null;
                $notificationEvent = new NotificationEvent($notification, $notificationType);

                $this->dispatcher->dispatch(NotificationEvent::NAME, $notificationEvent);
            }
        }
    }
    
    /**
     * 
     *  {@inheritdoc}
     */    
    public function supportsNotification(ExportParameters $exportParameters) {
        return true;
    }
}
