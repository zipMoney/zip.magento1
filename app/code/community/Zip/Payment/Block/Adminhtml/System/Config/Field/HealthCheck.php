<?php

use \zipMoney\ApiClient;
use \zipMoney\Configuration;

class Zip_Payment_Block_Adminhtml_System_Config_Field_HealthCheck extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $template = 'zip/payment/system/config/field/healthcheck.phtml';

    const STATUS_SUCCESS = 'success';
    const STATUS_WARNING = 'warning';
    const STATUS_ERROR = 'error';

    const SSL_DISABLED_MESSAGE = 'Your site does not have SSL Certificates';
    const CURL_EXTENSION_DISABLED = 'CURL extension has not been installed or disabled';
    const CURL_SSL_VERIFICATION_DISABLED_MESSAGE = 'CURL SSL Verification has been disabled';
    const API_CERTIFICATE_INVALID_MESSAGE = 'SSL Certificate is not valid for the API';
    const API_INACCESSIBLE_MESSAGE = 'Failed to access Zip Payment API';

    protected $result = array(
        'status' => self::STATUS_SUCCESS,
        'items' => array()
    );

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->template);
        }
        return $this;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->checkSystemHealth();
        $this->addData($this->result);
        return $this->_toHtml();
    }

    protected function checkSystemHealth() {

        $sslEnabled = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        $curlEnabled = function_exists('curl_version');

        if(!$sslEnabled) {
            $this->addFailedItem(self::STATUS_WARNING, self::SSL_DISABLED_MESSAGE);
        }

        if(!$curlEnabled) {
            $this->addFailedItem(self::STATUS_ERROR, self::CURL_EXTENSION_DISABLED);
        }
        else {
            $curl = curl_init();
            $curlSSLVerificationEnabled = curl_getinfo($curl, CURLOPT_SSL_VERIFYPEER) && curl_getinfo($curl, CURLOPT_SSL_VERIFYPEER);

            $config = Configuration::getDefaultConfiguration();
            $config->setEnvironment('production');
            $url = $config->getHost();

            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_URL, $url);

            if(!$curlSSLVerificationEnabled) {

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);

                $this->addFailedItem(self::STATUS_WARNING, self::CURL_SSL_VERIFICATION_DISABLED_MESSAGE);
            }

            try {
                curl_exec($curl);
                $sslVerified = curl_getinfo($curl, CURLINFO_SSL_VERIFYRESULT) == 0;
                $isAccessible = !empty(curl_getinfo($curl, CURLINFO_PRIMARY_IP)); 

                if(!$sslVerified) {
                    $this->addFailedItem(self::STATUS_WARNING, self::API_CERTIFICATE_INVALID_MESSAGE);
                }

                if(!$isAccessible) {
                    $this->addFailedItem(self::STATUS_ERROR, self::API_INACCESSIBLE_MESSAGE);
                }
                
            }
            catch(Exception $e) {
                $this->addFailedItem(self::STATUS_ERROR, self::API_INACCESSIBLE_MESSAGE);
            }
            
            curl_close($curl);
        }
        
    }

    protected function addFailedItem($status, $label) {

        if($this->result['status'] != self::STATUS_ERROR) {
            $this->result['status'] = $status;
        }

        $this->result['items'][] = array(
            "status" => $status,
            "label" => $label
        );

    }
}