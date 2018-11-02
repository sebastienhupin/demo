<?php

namespace AppBundle\Services\Messenger\Notifier;
use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;

/**
 *
 * @author sebastienhupin
 */
interface NotifierInterface {
    /**
     * Send notification
     * 
     * @param MessageCollection<\AppBundle\Services\Messenger\Message> $message
     * @param Parameters $parameters
     */
    public function notify(MessageCollection $messages, Parameters $parameters = null); 
    /**
     * Checks whether the given class is supported to notify the given type.
     *
     * @param Parameters $parameters
     *
     * @return bool
     */
    public function supportsNotification(Parameters $parameters);
}
