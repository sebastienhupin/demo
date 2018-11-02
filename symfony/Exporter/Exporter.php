<?php

namespace AppBundle\Services\Exporter;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use AppBundle\Services\AccessService;
use AppBundle\Model\Export\Parameters;
use AppBundle\Services\Exporter\Encoder\EncoderInterface;
use AppBundle\Services\Exporter\Normalizer\NormalizerInterface;
use AppBundle\Services\Exporter\Storage\StorageInterface;
use AppBundle\Services\Exporter\Output\OutputInterface;
use AppBundle\Services\Exporter\Notifier\NotifierInterface;
use AppBundle\Services\Exporter\Filter\FilterInterface;

/**
 * Description of Exporter
 *
 * @author sebastienhupin
 */
class Exporter {    
    /**
     *
     * @var Array 
     */
    private $encoders;        
    /**
     *
     * @var Array 
     */
    private $normalizers;    
    /**
     *
     * @var Array 
     */
    private $storages;   
    /**
     *
     * @var Array 
     */
    private $outputs;
    /**
     *
     * @var Array 
     */
    private $notifiers;  
    /**
     *
     * @var Array
     */
    private $filters;
    /**
     *
     * @var boolean 
     */
    private $detachedProcess;            
    /**
     *
     * @var Producer 
     */
    private $exportProducer;        
    /**
     *
     * @var AccessService 
     */
    private $accessService;
    /**
     *
     * @var Parameters 
     */
    private $exportParameters;

    private $errorNormalizer;
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     * @param Producer $exportProducer
     */
    public function __construct(AccessService $accessService, Producer $exportProducer)
    {
        $this->accessService = $accessService;
        $this->exportProducer = $exportProducer;      
    }

    /**
     * Start the export
     *
     * @param array $data
     * @return DocumentCollection
     */
    public function export(Array $data) {
        $forceDirectExport = $this->getExportParameters()->getForceDirectExport();

        if (!$forceDirectExport && $this->isDetachedProcess()) {
            $this->detachedExport($data);
        }
        else {
            return $this->directExport($data);
        }
    }

    /**
     * Gets the export definition.
     * 
     * @return Parameters 
     */
    public function getExportParameters() {
        return $this->exportParameters;
    }
    
    /**
     * Sets the export definition.
     * 
     * @param Parameters  $exportParameters
     * @return $this
     */
    public function setExportParameters(Parameters $exportParameters) {
        $this->exportParameters = $exportParameters;
        if (null === $this->exportParameters->getAccessId()) {
            $this->exportParameters->setAccessId($this->accessService->getAccess()->getId());
        }
        if (null === $this->exportParameters->getOrganizationId()) {
            $this->exportParameters->setOrganizationId($this->accessService->getAccess()->getOrganization()->getId());
        }
        return $this;
    }

    /**
     * Is detachedProcess
     * 
     * @return boolean
     */
    public function isDetachedProcess() {
        return $this->detachedProcess;
    }

    /**
     * Sets the process run in background is used.
     * 
     * @param boolean $detachedProcess
     * @return $this
     */
    public function setDetachedProcess($detachedProcess) {
        $this->detachedProcess = $detachedProcess;
        return $this;
    }
    
    /**
     * Direct processing
     * 
     * @param array $data
     */
    public function directExport(Array $data) {
        $this->exportParameters->setData($data);
        return $this->process();
    }
    
    /**
     * Detached processing
     * 
     * @param array $data
     */
    public function detachedExport(Array $data) {
        $this->exportParameters->setData($data);        
        $this->exportProducer->publish(serialize($this->exportParameters));        
    }    
    
    /**
     * Process the data
     * 
     * @return DocumentCollection<Document>
     */
    public function process() {
        /**
         * @var Array
         */
        $data = $this->filtering($this->exportParameters->getData());
        /*
         * @var DocumentCollection<Document>
         */
        $documentCollection = $this->normalize($data);

        $this->encode($documentCollection);

        $this->store($documentCollection);

        $this->output($documentCollection);

        if(!$this->getExportParameters()->getForceDirectExport()){
            $this->notify($documentCollection, $this->errorNormalizer);
        }

        return $documentCollection;
    }
    
    /**
     * Filtering
     * 
     * @param array $data
     * @return type
     * @throws \RuntimeException
     */
    private function filtering(Array $data) {
        $filter = $this->getFilter();
        if (null === $filter) {
            throw new \RuntimeException('No filter found');
        }
        
        return $filter->filtering($this->exportParameters, $data);
    }
    
    /**
     * Normalize
     * 
     * @param array $data
     * @return DocumentCollection<Document>
     * @throws \RuntimeException
     */
    private function normalize(Array $data) {
        // Normalize data.        
        $normalizer = $this->getNormalizer();
        if (null === $normalizer) {
            throw new \RuntimeException('No normalizer found');
        }
        
        $documentCollection = $normalizer->normalize($this->exportParameters, $data);
        $this->errorNormalizer = $normalizer->getErrors();

        return $documentCollection;
    }
    
    /**
     * Encode
     * 
     * @param DocumentCollection<Document> $documentCollection
     * @throws \RuntimeException
     */
    private function encode(DocumentCollection $documentCollection) {
        // Call the encoder        
        $encoder = $this->getEncoder();
        if (null === $encoder) {
            throw new \RuntimeException('No encoder found');
        }
        
        $encoder->encode($documentCollection, $this->exportParameters);
    }
    
    /**
     * Store
     * 
     * @param DocumentCollection<Document> $documentCollection
     * 
     * @return Array<\AppBundle\Entity\Core\File>
     * @throws \RuntimeException
     */
    private function store(DocumentCollection $documentCollection) {
        // Call the storage
        $storage = $this->getStorage();        
        if (null === $storage) {
            throw new \RuntimeException('No storage found');
        }
        
        $files = $storage->store($documentCollection, $this->exportParameters);

        return $files;
    }
    
    /**
     * Output files
     * 
     * @param DocumentCollection<Document> $documentCollection
     * @throws \RuntimeException
     */
    private function output(DocumentCollection $documentCollection) {
        // Call the output
        $output = $this->getOutput();    
        if (null === $output) {
            throw new \RuntimeException('No output found');
        }
        
        $output->output($documentCollection, $this->exportParameters);
    }

    /**
     * Notify
     *
     * @param DocumentCollection $documentCollection
     * @param ErrorCollection|null $errors
     */
    private function notify(DocumentCollection $documentCollection, ErrorCollection $errors) {
        
        // Call the notifier
        $notifier = $this->getNotifier();    
        if (null === $notifier) {
            throw new \RuntimeException('No notifier found');
        }        
        
        $notifier->notify($documentCollection, $this->exportParameters, $errors);
    }

    /**
     * Returns a matching encoder.
     * 
     * @return EncoderInterface|null
     */
    private function getEncoder() {
        foreach ($this->encoders as $encoder) {
            if ($encoder instanceof EncoderInterface && $encoder->supportsEncoding($this->exportParameters)) {
                return $encoder;
            }
        }        
    }
    
    /**
     * Gets encoders
     * 
     * @return array
     */
    public function getEncoders() {
        return $this->encoders;
    }
    
    /**
     * Sets encoders.
     * 
     * @param Array $encoders
     * @return $this
     */
    public function setEncoders(Array $encoders) {
        $this->encoders = $encoders;
        return $this;
    }    
    
    /**
     * Returns a matching formater.
     * 
     * @return NormalizerInterface|null
     */
    private function getNormalizer() {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface && $normalizer->supportsNormalization($this->exportParameters)) {
                return $normalizer;
            }
        }        
    }    
        
    /**
     * Gets normalizers
     * 
     * @return Array
     */
    public function getNormalizers() {
        return $this->normalizers;
    }
    
    /**
     * Sets normalizers
     * 
     * @param array $normalizers
     * @return $this
     */
    public function setNormalizers(Array $normalizers) {
        $this->normalizers = $normalizers;
        return $this;
    }
    
    /**
     * Returns a matching storage.
     * 
     * @return StorageInterface|null
     */
    private function getStorage() {
        foreach ($this->storages as $storage) {
            if ($storage instanceof StorageInterface && $storage->supportsStorage($this->exportParameters)) {
                return $storage;
            }
        }        
    }    

    /**
     * Gets storages
     * 
     * @return Array
     */
    public function getStorages() {
        return $this->storages;
    }
    
    /**
     * Sets storages
     * 
     * @param array $storages
     * @return $this
     */
    public function setStorages(Array $storages) {
        $this->storages = $storages;
        return $this;
    }

    /**
     * Gets outputs
     * 
     * @return Array
     */
    public function getOutputs() {
        return $this->outputs;
    }
    
    /**
     * Returns a matching output.
     * 
     * @return OutputInterface|null
     */
    private function getOutput() {
        foreach ($this->outputs as $output) {
            if ($output instanceof OutputInterface && $output->supportsOutput($this->exportParameters)) {
                return $output;
            }
        }        
    }    
    
    /**
     * Sets outputs
     * 
     * @param array $outputs
     * @return $this
     */
    public function setOutputs(Array $outputs) {
        $this->outputs = $outputs;
        return $this;
    }
    
    /**
     * Gets notifiers
     * 
     * @return Array
     */
    public function getNotifiers() {
        return $this->notifiers;
    }
    
    /**
     * Returns a matching output.
     * 
     * @return NotifierInterface|null
     */
    private function getNotifier() {
        foreach ($this->notifiers as $notifier) {
            if ($notifier instanceof NotifierInterface && $notifier->supportsNotification($this->exportParameters)) {
                return $notifier;
            }
        }        
    }    
    
    /**
     * Sets notifiers
     * 
     * @param array $notifiers
     * @return $this
     */
    public function setNotifiers(Array $notifiers) {
        $this->notifiers = $notifiers;
        return $this;
    }
    
    /**
     * Gets filters
     * 
     * @return Array
     */
    public function getFilters() {
        return $this->filters;
    }
    
    /**
     * Returns a matching filter.
     * 
     * @return FilterInterface|null
     */
    private function getFilter() {
        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterInterface && $filter->supportsFilter($this->exportParameters)) {
                return $filter;
            }
        }        
    }    
    
    /**
     * Sets filters
     * 
     * @param array $filters
     * @return $this
     */
    public function setFilters(Array $filters) {
        $this->filters = $filters;
        return $this;
    }    
}
