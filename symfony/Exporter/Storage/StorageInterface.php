<?php

namespace AppBundle\Services\Exporter\Storage;

use AppBundle\Model\Export\Parameters as ExportParameters;
use AppBundle\Services\Exporter\DocumentCollection;

/**
 * 
 * @author sebastienhupin
 */
interface StorageInterface {
    
    /**
     * Store the given documentCollection
     * 
     * @param DocumentCollection $documentCollection
     * @param ExportParameters $exportParameters
     * 
     */
    public function store(DocumentCollection $documentCollection, ExportParameters $exportParameters = null);
    
    /**
     * Checks whether the given class is supported to store the given type.
     *
     * @param ExportParameters $exportParameters
     *
     * @return bool
     */
    public function supportsStorage(ExportParameters $exportParameters);    
}
