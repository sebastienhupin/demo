<?php

namespace AppBundle\Services\Exporter\Encoder;
use AppBundle\Services\Exporter\DocumentCollection;
use AppBundle\Model\Export\Parameters as ExportParameters;

class DefaultEncoder implements EncoderInterface {

    /**
     * {@inheritdoc}
     */
    public function encode(DocumentCollection $documentCollection, ExportParameters $exportParameters = null) {

    }

    /**
     * {@inheritdoc}
     */    
    public function supportsEncoding(ExportParameters $exportParameters) {
        return true;
    }
}
