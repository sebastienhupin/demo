<?php

namespace AppBundle\Services\Messenger\Carrier;

use AppBundle\Services\Messenger\MessageCollection;
use AppBundle\Services\Messenger\Parameters;

interface CarrierInterface
{
  /**
   * Send a collection of messages
   * 
   * @param MessageCollection $messages
   */
  public function send(MessageCollection $messages, Parameters $parameters, $delivery = true);
  
  /**
   * Check a collection of messages
   * 
   * @param MessageCollection $messages
   */
  public function check(MessageCollection $messages, Parameters $parameters);  
  
  /**
   * Checks whether the carrier can send the given message.
   * 
   * @param Parameters $parameters
   * @return bool
   */
  public function supportsMessage(Parameters $parameters);
}