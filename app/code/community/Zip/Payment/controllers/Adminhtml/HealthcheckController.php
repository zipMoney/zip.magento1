<?php

/**
 * Health Check controller
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Adminhtml_HealthcheckController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Zip_Payment_Model_Logger
     */
    protected $_logger = null;

    public function checkAction()
    {
        $apiKey = $this->getRequest()->getParam(Zip_Payment_Model_Config::URL_PARAM_API_KEY);
        $publicKey = $this->getRequest()->getParam(Zip_Payment_Model_Config::URL_PARAM_PUBLIC_KEY);
        $environment = $this->getRequest()->getParam(Zip_Payment_Model_Config::URL_PARAM_ENVIRONMENT);
        $websiteCode = (string)$this->getRequest()->getParam('website', 0);
        if (preg_match('/^[\*]+$/m', $apiKey)) {
            $apiKey = null;
        }
        if (preg_match('/^[\*]+$/m', $publicKey)) {
            $publicKey = null;
        }
        $environmentList = $this->getEnvironmentList();
        if (!in_array($environment, $environmentList)) {
            $environment = null;
        }
        $healthCheck = Mage::getModel('zip_payment/adminhtml_system_config_backend_healthCheck');
        $result = $healthCheck->getHealthResult($websiteCode, $apiKey, $publicKey, $environment);
        Mage::app()->getResponse()->setBody(json_encode($result));
    }

    /**
     * get environment list
     * @return array
     */
    private function getEnvironmentList() {
        $result = array(
            Zip_Payment_Model_Config::PRODUCTION,
            Zip_Payment_Model_Config::SANDBOX
        );

        return $result;
    }

    /**
     * Get logger object
     *
     * @return Zip_Payment_Model_Logger
     */
    public function getLogger()
    {
        if ($this->_logger == null) {
            $this->_logger = Mage::getSingleton('zip_payment/logger');
        }

        return $this->_logger;
    }

}

