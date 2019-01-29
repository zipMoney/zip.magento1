<?php

/**
 * Admin Model of health check
 *
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck extends Mage_Core_Model_Config_Data
{

    const STATUS_SUCCESS = 1;
    const STATUS_WARNING = 2;
    const STATUS_ERROR = 3;

    const SSL_DISABLED_MESSAGE = 'Your site does not have SSL Certificates';
    const CURL_EXTENSION_DISABLED = 'CURL extension has not been installed or disabled';
    const CURL_SSL_VERIFICATION_DISABLED_MESSAGE = 'CURL SSL Verification has been disabled';
    const API_CERTIFICATE_INVALID_MESSAGE = 'SSL Certificate is not valid for the API';
    const API_INACCESSIBLE_MESSAGE = 'Failed to access Zip Payment API';
    const API_PRIVATE_KEY_INVALID_MESSAGE = 'Your API private key is empty or invalid';
    const API_PUBLIC_KEY_INVALID_MESSAGE = 'Your API public key is empty or invalid';
    const API_CREDENTIAL_INVALID_MESSAGE = 'Your API credential is invalid';
    const MERCHANT_COUNTRY_NOT_SUPPORTED_MESSAGE = 'Your merchant country not been supported';

    const CONFIG_PRIVATE_KEY_PATH = 'payment/zip_payment/private_key';
    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';

    protected $result = array(
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

        $sslEnabled = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        $curlEnabled = function_exists('curl_version');
        $publicKey = $config->getValue(self::CONFIG_PUBLIC_KEY_PATH);
        $privateKey = $config->getValue(self::CONFIG_PRIVATE_KEY_PATH);

        // check if private key is empty
        if(empty($privateKey)) {
            $this->appendFailedItem(self::STATUS_ERROR, self::API_PRIVATE_KEY_INVALID_MESSAGE);
        }

        // check if public key is empty
        if(empty($publicKey)) {
            $this->appendFailedItem(self::STATUS_ERROR, self::API_PUBLIC_KEY_INVALID_MESSAGE);
        }

        // check if current merchant country been supported
        if(!$config->isMerchantCountrySupported()) {
            $this->appendFailedItem(self::STATUS_ERROR, self::MERCHANT_COUNTRY_NOT_SUPPORTED_MESSAGE);
        }

        // check whether SSL is enabled
        if(!$sslEnabled) {
            $this->appendFailedItem(self::STATUS_WARNING, self::SSL_DISABLED_MESSAGE);
        }

        // check whether CURL is enabled ot not
        if(!$curlEnabled) {
            $this->appendFailedItem(self::STATUS_ERROR, self::CURL_EXTENSION_DISABLED);
        }
        else {
            $curl = curl_init();

            $curlSSLVerificationEnabled = curl_getinfo($curl, CURLOPT_SSL_VERIFYPEER) && curl_getinfo($curl, CURLOPT_SSL_VERIFYPEER);
            $apiConfig = Mage::getSingleton('zip_payment/api_configuration')->generateApiConfiguration();
            $url = $apiConfig->getHost();

            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_URL, $url);

            // if SSL verification is disabled
            if (!$curlSSLVerificationEnabled) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

                $this->appendFailedItem(self::STATUS_WARNING, self::CURL_SSL_VERIFICATION_DISABLED_MESSAGE);
            }

            try {
                $headers = array(
                    'Authorization: ' . $apiConfig->getApiKeyPrefix('Authorization') . ' ' . $apiConfig->getApiKey('Authorization'),
                    'Content-Type: application/json',
                    'Zip-Version: 2017-03-01'
                );

                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_exec($curl);

                $sslVerified = curl_getinfo($curl, CURLINFO_SSL_VERIFYRESULT) == 0;
                $isAccessible = !empty(curl_getinfo($curl, CURLINFO_PRIMARY_IP));
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                // if API certification invalid
                if(!$sslVerified) {
                    $this->appendFailedItem(self::STATUS_WARNING, self::API_CERTIFICATE_INVALID_MESSAGE);
                }

                // if API server is inaccessible
                if(!$isAccessible) {
                    $this->appendFailedItem(self::STATUS_ERROR, self::API_INACCESSIBLE_MESSAGE);
                }

                // if API credential is invalid
                if($httpCode == '401') {
                    $this->appendFailedItem(self::STATUS_ERROR, self::API_CREDENTIAL_INVALID_MESSAGE);
                }
            }
            catch(Exception $e) {
                $this->appendFailedItem(self::STATUS_ERROR, self::CONFIG_PRIVATE_KEY_PATH);
            }

            curl_close($curl);
        }

        usort(
            $this->result['items'], function ($a, $b) {
                return $b['status'] - $a['status'];
            }
        );

        return $this->result;

    }

    /**
     * append failed item into health result
     */
    protected function appendFailedItem($status, $label)
    {
        if(!is_null($status) && $this->result['overall_status'] < $status) {
            $this->result['overall_status'] = $status;
        }

        $this->result['items'][] = array(
            "status" => $status,
            "label" => $label
        );

    }

}