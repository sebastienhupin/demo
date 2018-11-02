<?php

namespace AppBundle\Services\Messenger\Automatic;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use AppBundle\Services\AccessService;
use AppBundle\Model\AutomaticMessenger\Parameters;
use AppBundle\Services\Messenger\Automatic\Filter\FilterInterface;
use AppBundle\Services\Messenger\Automatic\Normalizer\NormalizerInterface;
use AppBundle\Services\Messenger\Automatic\Output\OutputInterface;
use AppBundle\Services\Messenger\MessageCollection;

/**
 * Class AutomaticMessenger
 * @package AppBundle\Services\Reminder
 */
class AutomaticMessenger {
    /**
     *
     * @var AccessService 
     */
    private $accessService;
    /**
     *
     * @var Producer 
     */
    private $producer;
    /**
     *
     * @var Parameters 
     */
    private $parameters;  
    /**
     *
     * @var boolean 
     */
    private $detachedProcess;
    /**
     *
     * @var Array
     */
    private $filters;
    /**
     *
     * @var Array 
     */
    private $normalizers;    
    /**
     *
     * @var Array 
     */
    private $outputs;
    
    /**
     * The constructor
     * 
     * @param AccessService $accessService
     * @param Producer $producer
     */
    public function __construct(AccessService $accessService, Producer $producer)
    {
        $this->accessService = $accessService;
        $this->producer = $producer;      
    }

    /**
     * @param array $data
     * @return DocumentCollection
     */
    public function send(Array $data) {
        if ($this->isDetachedProcess()) {
            $this->detached($data);
        }
        else {
            return $this->direct($data);
        }
    }
    
    /**
     * Gets the parameters.
     * 
     * @return Parameters 
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * Sets the parameters definition.
     * 
     * @param Parameters  $parameters
     * @return $this
     */
    public function setParameters(Parameters $parameters) {
        $this->parameters = $parameters;
        if (null === $this->parameters->getAccessId()) {
            $this->parameters->setAccessId($this->accessService->getAccess()->getId());
        }
        if (null === $this->parameters->getOrganizationId()) {
            $this->parameters->setOrganizationId($this->accessService->getAccess()->getOrganization()->getId());
        }
        if (null !== $this->parameters->getMetaData()) {
            $this->parameters->setMetaData($this->parameters->getMetaData());
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
     * @param array $data
     * @return DocumentCollection
     */
    public function direct(Array $data) {
        $this->parameters->setData($data);
        return $this->process();
    }    
    
    /**
     * Detached processing
     * 
     * @param array $data
     */
    public function detached(Array $data) {
        $this->parameters->setData($data);        
        $this->producer->publish(serialize($this->parameters));        
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
        $data = $this->filtering($this->parameters->getData());

        /*
         * @var MessageCollection<Message>
         */
        $messages = $this->normalize($data);

        $this->output($messages);
        
        return $messages;
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
        
        return $filter->filtering($this->parameters, $data);
    }    
    
    /**
     * Normalize
     * 
     * @param array $data
     * @return MessageCollection<Message>
     * @throws \RuntimeException
     */
    private function normalize(Array $data) {
        // Normalize data.        
        $normalizer = $this->getNormalizer();
        if (null === $normalizer) {
            throw new \RuntimeException('No normalizer found');
        }
        
        $messages = $normalizer->normalize($this->parameters, $data);   
        
        return $messages;
    }    
    
    /**
     * Output
     * 
     * @param MessageCollection<Message> $messages
     * @throws \RuntimeException
     */
    private function output(MessageCollection $messages) {
        // Call the output
        $output = $this->getOutput();    
        if (null === $output) {
            throw new \RuntimeException('No output found');
        }
        
        $output->output($messages, $this->parameters);
    }    
    
    
    /**
     * Returns a matching normalizer.
     * 
     * @return NormalizerInterface|null
     */
    private function getNormalizer() {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface && $normalizer->supportsNormalization($this->parameters)) {
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
            if ($filter instanceof FilterInterface && $filter->supportsFilter($this->parameters)) {
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
            if ($output instanceof OutputInterface && $output->supportsOutput($this->parameters)) {
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
}
