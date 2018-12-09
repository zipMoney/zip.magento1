<?php

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

    protected function getHealthResult() {

        $sslEnabled = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        $curlEnabled = function_exists('curl_version');
        $publicKey = Mage::getSingleton('zip_payment/config')->getValue(self::CONFIG_PUBLIC_KEY_PATH);
        $privateKey = Mage::getSingleton('zip_payment/config')->getValue(self::CONFIG_PRIVATE_KEY_PATH);

        // check if public key is empty
        if(empty($publicKey)) {
            $this->appendFailedItem(self::STATUS_ERROR, self::API_PUBLIC_KEY_INVALID_MESSAGE);
        }

        // check if private key is empty
        if(empty($privateKey)) {
            $this->appendFailedItem(self::STATUS_ERROR, self::CONFIG_PRIVATE_KEY_PATH);
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
            $apiConfig = Mage::getSingleton('zip_payment/config')->getApiConfiguration();
            $url = $apiConfig->getHost();

            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_URL, $url);
            // https://www.blender.stackexchange.com/

            // if SSL verification is disabled
            if(!$curlSSLVerificationEnabled) {

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);

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

                if(!$sslVerified) {
                    $this->appendFailedItem(self::STATUS_WARNING, self::API_CERTIFICATE_INVALID_MESSAGE);
                }

                if(!$isAccessible) {
                    $this->appendFailedItem(self::STATUS_ERROR, self::API_INACCESSIBLE_MESSAGE);
                }

                if($httpCode == '401') {
                    $this->appendFailedItem(self::STATUS_ERROR, self::API_CREDENTIAL_INVALID_MESSAGE);
                }
                
            }
            catch(Exception $e) {
                $this->appendFailedItem(self::STATUS_ERROR, self::CONFIG_PRIVATE_KEY_PATH);
            }
            
            curl_close($curl);
        }

        usort($this->result['items'], function($a, $b) {
            return $b['status'] - $a['status'];
        });

        return $this->result;
        
    }

    protected function appendFailedItem($status, $label) {

        if(!is_null($status) && $this->result['overall_status'] < $status) {
            $this->result['overall_status'] = $status;
        }

        $this->result['items'][] = array(
            "status" => $status,
            "label" => $label
        );

    }

}