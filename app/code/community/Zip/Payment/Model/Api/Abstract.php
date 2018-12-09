<?php

use Zip\Model\Metadata;
use Zip\ApiException;

abstract class Zip_Payment_Model_Api_Abstract
{
    protected $api = null;
    protected $apiConfig = null;
    protected $logger = null;
    protected $response = null;

    public function __construct()
    {
        Mage::helper('zip_payment')->autoload();
    }

    public function setApiConfig($apiConfig) {
        $this->apiConfig = $apiConfig;
        return $this;
    }

    /**
     * Get logger object
     * @return Zip_Payment_Model_Logger
     */
    protected function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = Mage::getModel('zip_payment/logger');
        }
        return $this->logger;
    }

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('zip_payment');
    }

    /**
     * Get session namespace
     *
     * @return Zip_Payment_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('zip_payment/session');
    }

    protected function logException($e)
    {
        if ($e instanceof ApiException) {

            $message = $e->getMessage();
            $this->getLogger()->error("Api Error: " . $message);
            $respBody = $e->getResponseBody();

            if ($respBody) {
                $detail = json_encode($respBody);
                $this->getLogger()->error($detail);
            }
        }
    }

    /**
     * Returns the prepared metadata model
     * Dummy data as normal merchant don't need this
     * @return Zip\Model\Metadata
     */
    protected function getMetadata()
    {
        $metadata = new Metadata();
        return $metadata;
    }

    public function getResponse() {
        return $this->response;
    }

    abstract protected function getApi();

    abstract public function create();

    abstract protected function preparePayload();
    
}