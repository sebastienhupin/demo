<?php

namespace AppBundle\Services\Messenger\Notifier;

use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;

/**
 * Description of DefaultNotifier
 *
 * @author sebastienhupin
 */
class PreviewNotifier implements NotifierInterface {

    /**
     * PreviewNotifier constructor.
     */
    public function __construct()
    {

    }

    /**
     * 
     *  {@inheritdoc}
     */ 
    public function notify(MessageCollection $messages, Parameters $parameters = null) {
        return true;
    }
    
    /**
     * 
     *  {@inheritdoc}
     */    
    public function supportsNotification(Parameters $parameters) {
        return 'preview' === $parameters->getAction();
    }
}
