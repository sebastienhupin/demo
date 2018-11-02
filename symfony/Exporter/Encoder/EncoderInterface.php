<?php

namespace AppBundle\Services\Exporter\Encoder;

use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

interface EncoderInterface
{
  /**
   * Encode a document to the given format
   * 
   * @param DocumentCollection $documentCollection
   * @param ExportParameters $exportParameters
   */
  public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null);
  
  /**
   * Checks whether the encoder can encode the given format.
   * 
   * @param ExportParameters $exportParameters
   * @return bool
   */
  public function supportsEncoding(ExportParameters $exportParameters);
}