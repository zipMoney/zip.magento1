<?php

/**
 * Admin Model of health check
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck extends Mage_Core_Model_Config_Data
{

    const STATUS_SUCCESS = 1;
    const STATUS_WARNING = 2;
    const STATUS_ERROR = 3;
    const STATUS_OK = 0;

    const SSL_DISABLED_MESSAGE = 'Your store {store_name} ({store_url}) does not have SSL';
    const CURL_EXTENSION_DISABLED = 'CURL extension has not been installed or disabled';
    const API_CERTIFICATE_INVALID_MESSAGE = 'SSL Certificate is not valid for the API';
    const API_PRIVATE_KEY_INVALID_MESSAGE = 'Your API private key is empty or invalid';
    const API_PUBLIC_KEY_INVALID_MESSAGE = 'Your API public key is empty or invalid';
    const API_CREDENTIAL_INVALID_MESSAGE = 'Your API credential is invalid';
    const MERCHANT_COUNTRY_NOT_SUPPORTED_MESSAGE = 'Your merchant country not been supported';


    protected $_result = array(
        'overall_status' => self::STATUS_SUCCESS,
        'items' => array()
    );

    /**
     * check multiple items and get health result
     */
    public function getHealthResult($websiteCode, $apiKey = null, $publicKey = null, $env = null)
    {
        $config = Mage::helper('zip_payment')->getConfig();
        $logger = Mage::getSingleton('zip_payment/logger');
        $apiConfig = Mage::getSingleton('zip_payment/api_configuration')
            ->generateApiConfiguration();
        $website = Mage::getModel('core/website')->load( $websiteCode,'code' );
        $websiteId = (int)$website->getId();
        $storeId = Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
        $curlEnabled = function_exists('curl_version');
        $publicKey = $publicKey ? $publicKey : $config->getValue(Zip_Payment_Model_Config::CONFIG_PUBLIC_KEY_PATH,$storeId);
        $privateKey = $apiKey ? $apiKey : Mage::helper('core')
            ->decrypt($config->getValue(Zip_Payment_Model_Config::CONFIG_PRIVATE_KEY_PATH,$storeId));
        $environment = $env ? $env : $config->getValue(Zip_Payment_Model_Config::CONFIG_ENVIRONMENT_PATH,$storeId);
        $apiConfig->setApiKey('Authorization', $privateKey)
            ->setApiKeyPrefix('Authorization', 'Bearer')
            ->setEnvironment($environment);
        // check if private key is empty
        if (empty($privateKey)) {
            $this->appendItem(self::STATUS_ERROR, self::API_PRIVATE_KEY_INVALID_MESSAGE);
        }

        // check if public key is empty
        if (empty($publicKey)) {
            $this->appendItem(self::STATUS_ERROR, self::API_PUBLIC_KEY_INVALID_MESSAGE);
        }

        // check if current merchant country been supported
        if (!$config->isMerchantCountrySupported()) {
            $this->appendItem(self::STATUS_ERROR, self::MERCHANT_COUNTRY_NOT_SUPPORTED_MESSAGE);
        }

        // check whether SSL is enabled
        $this->checkStoreSSLSettings();

        // check whether CURL is enabled ot not
        if (!$curlEnabled) {
            $this->appendItem(self::STATUS_ERROR, self::CURL_EXTENSION_DISABLED);
        } else {
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(
                array(
                    'timeout' => 10
                )
            );

            try {
                $apiConfig->setCurlTimeout(30);
                $headers = array(
                    'Authorization: ' .
                    $apiConfig->getApiKeyPrefix('Authorization') .
                    ' ' .
                    $apiConfig->getApiKey('Authorization'),
                    'Accept : application/json',
                    'Zip-Version: 2017-03-01',
                    'Content-Type: application/json',
                    'Idempotency-Key: ' .uniqid()
                );
                $url = $apiConfig->getHost().'/me';
                $isAuEndpoint = false;
                // check api key length if it is more than or equal 50 then call SMI merchant info endpoint
                // otherwise call checkout get api endpoint only for Australia
                if (strlen($privateKey) <= 50) {
                    $checkoutId = 'au-co_PxSeQfLlpaYn6bLMZSMv13';
                    $url = $apiConfig->getHost().'/checkouts/'.$checkoutId;
                    $isAuEndpoint = true;
                }
                $curl->write(Zend_Http_Client::GET, $url, '1.1', $headers);
                $response = $curl->read();
                $sslVerified = $curl->getInfo(CURLINFO_SSL_VERIFYRESULT) == 0;
                $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
                $logger->debug('Response: '.json_encode($response));
                // if API certification invalid
                if (!$sslVerified) {
                    $this->appendItem(self::STATUS_WARNING, self::API_CERTIFICATE_INVALID_MESSAGE);
                }

                // if API credential is invalid
                if ($httpCode == '401') {
                    $this->appendItem(self::STATUS_ERROR, self::API_CREDENTIAL_INVALID_MESSAGE);
                }
                if ($httpCode == '200' && $isAuEndpoint == false) {
                    $result = preg_split('/^\r?$/m', $response, 2);
                    $result = trim($result[1]);
                    $data = json_decode($result);
                    $this->appendItem( self::STATUS_OK, ucfirst($environment)." Api key is for ".$data->name);
                    $regions = $data->regions;
                    if ($regions) {
                        $regionList = ' Valid for below regions '.ucfirst($environment).' environment:<br>';
                        $availableRegions = \Zip\Model\CurrencyUtil::getAvailableRegions();
                        foreach ($regions as $region) {
                            $regionList .= $availableRegions[$region].'<br>';
                        }
                        $this->appendItem(self::STATUS_OK, $regionList);
                    }
                }
                if ($httpCode == '404' || $httpCode =='200' && $isAuEndpoint == true){
                    $this->appendItem( self::STATUS_OK, " Api key valid for Australia region ".ucfirst($environment)." environment.");
                }
            }
            catch(Exception $e) {
                $this->appendItem(self::STATUS_ERROR, self::CONFIG_PRIVATE_KEY_PATH);
            }

            $curl->close();
        }

        usort(
            $this->_result['items'], function ($a, $b) {
            return $b['status'] - $a['status'];
        }
        );

        return $this->_result;

    }

    protected function checkStoreSSLSettings()
    {
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if ($store->getIsActive() !== '1'
                        || Mage::getStoreConfig(Zip_Payment_Model_Config::CONFIG_ACTIVE_PATH, $store->getStoreId()) !== '1'
                    ) {
                        continue;
                    }

                    $storeSecureUrl = Mage::getStoreConfig(
                        Mage_Core_Model_Url::XML_PATH_SECURE_URL, $store->getStoreId()
                    );
                    $url = parse_url($storeSecureUrl);

                    if ($url['scheme'] !== 'https') {
                        $message = self::SSL_DISABLED_MESSAGE;
                        $message = str_replace('{store_name}', $store->getName(), $message);
                        $message = str_replace('{store_url}', $storeSecureUrl, $message);

                        $this->appendItem(
                            self::STATUS_WARNING,
                            $message
                        );
                    }
                }
            }
        }
    }


    /**
     * append success and failed item into health result
     */
    protected function appendItem($status, $label)
    {
        if ($status !== null && $this->_result['overall_status'] < $status) {
            $this->_result['overall_status'] = $status;
        }

        $this->_result['items'][] = array(
            "status" => $status,
            "label" => $label
        );

    }


    /**
     * Get logger object
     *
     * @return Zip_Payment_Model_Logger
     */
    protected function getLogger()
    {
        if ($this->_logger == null) {
            $this->_logger = Mage::getModel('zip_payment/logger');
        }

        return $this->_logger;
    }


}
