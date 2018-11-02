<?php

namespace AppBundle\Services\Exporter\Normalizer;

use AppBundle\Services\Exporter\ErrorCollection;
use Symfony\Component\Templating\EngineInterface;
use AppBundle\Services\AccessService;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractNormalizerExporter{
    /**
     *
     * @var EngineInterface
     */
    protected $templating;

    /**
     *
     * @var AccessService
     */
    protected $accessService;
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ErrorCollection
     */
    protected $errorCollection;

    /**
     * The constructor
     *
     * @param EngineInterface $templating
     * @param AccessService $accessService
     */
    public function __construct(EngineInterface $templating, AccessService $accessService, RouterInterface $router) {
        $this->templating = $templating;
        $this->accessService = $accessService;
        $this->router = $router;
        $this->errorCollection = new ErrorCollection();
    }


    public function getErrors(){
        return $this->errorCollection;
    }

    /**
     * @param $options
     * @param $metaData
     * @param $organization
     * @return null|string
     * @throws \Exception
     */
    protected function getHeader($options,$metaData,$organization){
        $header = null;
        if(!empty($options['header'])){
            $logoPath = $organization->getLogo() ? $this->router->generate('opentalent_internal_secure_file_donwload', array('id' => $organization->getLogo()->getId())) : null;
            $header = $this->templating->render('@template/Export/list/list.header.html.twig',
                Array(
                    'organization' => $this->accessService->getAccess()->getOrganization(),
                    'options' => $options,
                    'title' => array_key_exists('title', $metaData) ? $metaData['title'] : null,
                    'logo'=> $logoPath ));
        }
        return $header;
    }

    /**
     * @param $options
     * @param $format
     * @return null|string
     * @throws \Exception
     */
    protected function getFooter($options,$format){
        $footer = null;
        if(!empty($options['footer'])){
            $footer = $this->templating->render('@template/Export/list/list.footer.html.twig',
                Array('organization' => $this->accessService->getAccess()->getOrganization(), 'options' => $options, 'format' => $format));
        }
        return $footer;
    }
}