<?php

/**
 * Description of OpentalentService
 *
 * @author Sébastien Hupin <sebastien.hupin at gmail.com>
 */

namespace Opentalent\OtWebservice\WebServices;

use Opentalent\OtWebservice\Domain\Model\Dto\ISearch;

class OpentalentService {

    /**
     * The service name
     * 
     * @var String 
     */
    protected $name = NULL;

    /**
     *
     * @var String 
     */
    protected $url = NULL;

    /**
     * Constructor
     */
    public function __construct() {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ot_cms']);
        $this->url = sprintf("%s%s/%s", $extensionConfiguration['api.']['url'], $extensionConfiguration['api.']['public'], $this->name);
    }

    /**
     * Gets a collection
     * 
     * @param ISearch $search
     * @return Array<stdClass>
     */
    public function cget(ISearch $search) {
        
        $urlParts = array(
            $this->url,
            http_build_query($this->getColletionParams($search))
        );

        $this->url = $url = implode('?', $urlParts);

        $data = $this->launch($url, $this->createContext('GET'));

        return $data['content'];
    }

    /**
     * Get an item
     * 
     * @param Integer $id
     * @return stdClass
     */
    public function get($id) {
        
        $url = sprintf('%s/%d',$this->url, $id);        
        $data = $this->launch($url, $this->createContext('GET'));

        return $data['content'];
    }
    
    /**
     * Gets parameters for a collection
     * 
     * @param ISearch $search
     * @return array
     */
    protected function getColletionParams(ISearch $search) {
        $params = array(
            'filter' => array(
                'where' => $search->filters,
                'order' => $search->orders
            ),
            'itemsPerPage' => $search->itemsPerPage
        );
        return $params;
    }
    
    /**
     * Create the resource context
     * 
     * @param String $method
     * @param String $content
     * @return resource A stream context resource
     */
    protected function createContext($method, $content = null) {
        $opts = array(
            'http' => array(
                'method' => $method,
                'header' => 'Content-type: application/x-www-form-urlencoded'
            )
        );

        if ($content !== null) {
            if (is_array($content)) {
                $content = http_build_query($content);
            }
            $opts['http']['content'] = $content;
        }

        return stream_context_create($opts);
    }

    /**
     * Call the api
     * 
     * @param string $url
     * @param resource $context
     * @return boolean
     */
    protected function launch($url, $context) {
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url );

        // Execute
        $result=curl_exec($ch);
        if($result === false)
        {
            echo 'Erreur Curl : ' . curl_error($ch);
        }
        // Closing
        curl_close($ch);

        return array('content' => json_decode($result));

//        die;
//        if (($stream = fopen($url, 'r', false, $context)) !== false) {
//            $content = stream_get_contents($stream);
//            $header = stream_get_meta_data($stream);
//            fclose($stream);
//            return array('content' => json_decode($content), 'header' => $header);
//        } else {
//            return false;
//        }
    }
}
