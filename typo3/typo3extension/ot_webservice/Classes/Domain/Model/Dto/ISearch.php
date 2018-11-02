<?php

namespace Opentalent\OtWebservice\Domain\Model\Dto;
/**
 *
 * @author sebastienhupin
 */
interface ISearch {
  /**
   * 
   * @param string $name
   * @return mixed or null
   */
  public function __get($name);
  
}
