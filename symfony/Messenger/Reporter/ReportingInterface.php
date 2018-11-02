<?php

namespace AppBundle\Services\Messenger\Reporter;

use AppBundle\Services\Messenger\Parameters;
use AppBundle\Services\Messenger\MessageCollection;

/**
 *
 * @author sebastienhupin
 */
interface ReportingInterface {
    
  /**
   * Report a message
   * 
   * @param MessageCollection $messages
   */
  public function reporting(MessageCollection $messages, Parameters $parameters);    
    
  /**
   * Checks whether the reporter can report the given message.
   * 
   * @param Parameters $parameters
   * @return bool
   */
  public function supportsMessage(Parameters $parameters);
}
