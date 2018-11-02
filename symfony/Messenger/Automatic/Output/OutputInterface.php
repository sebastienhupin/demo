<?php

namespace AppBundle\Services\Messenger\Automatic\Output;
use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Model\AutomaticMessenger\Parameters;

/**
 *
 * @author sebastienhupin
 */
interface OutputInterface {
    /**
     * Output the given files
     * 
     * @param MessageCollection<\AppBundle\Services\Messenger\Message> $messages
     * @param Parameters $parameters
     */
    public function output(MessageCollection $messages, Parameters $parameters);
    
    /**
     * Checks whether the given class is supported to output the given type.
     *
     * @param Parameters $parameters
     *
     * @return bool
     */
    public function supportsOutput(Parameters $parameters);     
}
