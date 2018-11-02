<?php

namespace AppBundle\Services\Messenger\Notifier;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use AppBundle\Event\Core\NotificationEvent;
use AppBundle\Entity\Core\Notification;
use AppBundle\Enum\Core\NotificationTypeEnum;
use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;

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
     * The constructor
     * 
     * @param UrlGeneratorInterface $urlGenerator
     * @param EventDispatcherInterface $dispatcher     
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EventDispatcherInterface $dispatcher = null)
    {
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher = $dispatcher;        
    }

    /**
     * 
     *  {@inheritdoc}
     */ 
    public function notify(MessageCollection $messages, Parameters $parameters = null) {
        
        $message = $messages->first();
        
        $link = null;
        $text = [ 'about' => $message->getAbout() ];
        if (null !== $message->getFile()) {
            $link = $this->urlGenerator->generate('opentalent_file_donwload', array('id' => $message->getFile()->getId()));
            $text['action'] = $parameters->getAction();
        }
        // Send a notification event
        $notification = new Notification();
        $notification->setName('message')
                ->setRecipientAccess($message->getAccess())
                ->setRecipientOrganization($message->getOrganization())
                ->setMessage($text)
                ->setLink($link)
                ->setType(NotificationTypeEnum::MESSAGE);
        
        $notificationType = null;
        $notificationEvent = new NotificationEvent($notification, $notificationType);

        $this->dispatcher->dispatch(NotificationEvent::NAME, $notificationEvent);        

    }
    
    /**
     * 
     *  {@inheritdoc}
     */    
    public function supportsNotification(Parameters $parameters) {
        return true;
    }
}
