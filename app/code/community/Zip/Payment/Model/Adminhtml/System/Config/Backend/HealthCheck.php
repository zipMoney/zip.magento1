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

    const SSL_DISABLED_MESSAGE = 'Your store {store_name} ({store_url}) does not have SSL';
    const CURL_EXTENSION_DISABLED = 'CURL extension has not been installed or disabled';
    const API_CERTIFICATE_INVALID_MESSAGE = 'SSL Certificate is not valid for the API';
    const API_PRIVATE_KEY_INVALID_MESSAGE = 'Your API private key is empty or invalid';
    const API_PUBLIC_KEY_INVALID_MESSAGE = 'Your API public key is empty or invalid';
    const API_CREDENTIAL_INVALID_MESSAGE = 'Your API credential is invalid';
    const MERCHANT_COUNTRY_NOT_SUPPORTED_MESSAGE = 'Your merchant country not been supported';

    const CONFIG_PRIVATE_KEY_PATH = 'payment/zip_payment/private_key';
    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';

    protected $_result = array(
        'overall_status' => self::STATUS_SUCCESS,
        'items' => array()
    );

    protected function _afterLoad()
    {
        $result = $this->getHealthResult();
        $this->setValue($result);
    }

    /**
     * check multiple items and get health result
     */
    protected function getHealthResult()
    {
        $config = Mage::helper('zip_payment')->getConfig();
        $apiConfig = Mage::getSingleton('zip_payment/api_configuration')
                ->generateApiConfiguration();

        $curlEnabled = function_exists('curl_version');
        $publicKey = $config->getValue(self::CONFIG_PUBLIC_KEY_PATH);
        $privateKey = $config->getValue(self::CONFIG_PRIVATE_KEY_PATH);

        // check if private key is empty
        if (empty($privateKey)) {
            $this->appendFailedItem(self::STATUS_ERROR, self::API_PRIVATE_KEY_INVALID_MESSAGE);
        }

        // check if public key is empty
        if (empty($publicKey)) {
            $this->appendFailedItem(self::STATUS_ERROR, self::API_PUBLIC_KEY_INVALID_MESSAGE);
        }

        // check if current merchant country been supported
        if (!$config->isMerchantCountrySupported()) {
            $this->appendFailedItem(self::STATUS_ERROR, self::MERCHANT_COUNTRY_NOT_SUPPORTED_MESSAGE);
        }

        // check whether SSL is enabled
        $this->checkStoreSSLSettings();

        // check whether CURL is enabled ot not
        if (!$curlEnabled) {
            $this->appendFailedItem(self::STATUS_ERROR, self::CURL_EXTENSION_DISABLED);
        } else {
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(
                array(
                    'timeout' => 10
                )
            );

            try {
                $headers = array(
                    'Authorization: ' .
                    $apiConfig->getApiKeyPrefix('Authorization') .
                    ' ' .
                    $apiConfig->getApiKey('Authorization'),
                    'Content-Type: application/json',
                    'Zip-Version: 2017-03-01'
                );

                $curl->write(Zend_Http_Client::GET, $apiConfig->getHost(), '1.1', $headers);

                $sslVerified = $curl->getInfo(CURLINFO_SSL_VERIFYRESULT) == 0;
                $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);

                // if API certification invalid
                if (!$sslVerified) {
                    $this->appendFailedItem(self::STATUS_WARNING, self::API_CERTIFICATE_INVALID_MESSAGE);
                }

                // if API credential is invalid
                if ($httpCode == '401') {
                    $this->appendFailedItem(self::STATUS_ERROR, self::API_CREDENTIAL_INVALID_MESSAGE);
                }
            }
            catch(Exception $e) {
                $this->appendFailedItem(self::STATUS_ERROR, self::CONFIG_PRIVATE_KEY_PATH);
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

                        $this->appendFailedItem(
                            self::STATUS_WARNING,
                            $message
                        );
                    }
                }
            }
        }
    }


    /**
     * append failed item into health result
     */
    protected function appendFailedItem($status, $label)
    {
        if ($status !== null && $this->_result['overall_status'] < $status) {
            $this->_result['overall_status'] = $status;
        }

        $this->_result['items'][] = array(
            "status" => $status,
            "label" => $label
        );

    }

}