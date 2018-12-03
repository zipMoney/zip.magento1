<?php

class Zip_Payment_Block_Adminhtml_System_Config_Field_HealthCheck extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $template = 'zip/payment/system/config/field/health_check.phtml';

    const STATUS_SUCCESS = 1;
    const STATUS_WARNING = 2;
    const STATUS_ERROR = 3;

    const SSL_DISABLED_MESSAGE = 'Your site does not have SSL Certificates';
    const CURL_EXTENSION_DISABLED = 'CURL extension has not been installed or disabled';
    const CURL_SSL_VERIFICATION_DISABLED_MESSAGE = 'CURL SSL Verification has been disabled';
    const API_CERTIFICATE_INVALID_MESSAGE = 'SSL Certificate is not valid for the API';
    const API_INACCESSIBLE_MESSAGE = 'Failed to access Zip Payment API';
    const API_PRIVATE_KEY_INVALID_MESSAGE = 'Please enter a valid private key';
    const API_PUBLIC_KEY_INVALID_MESSAGE = 'Please enter a valid public key';
    const API_CREDENTIAL_INVALID_MESSAGE = 'API credential keys are invalid';

    const CONFIG_PRIVATE_KEY_PATH = 'payment/zip_payment/private_key';
    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';

    const HEALTH_CHECK_CACHE_ID = 'zip_payment_health_check';

    protected $result = array(
        'overall_status' => self::STATUS_SUCCESS,
        'items' => array()
    );

    
    public function getStatusLabel($statusLevel = null)
    {
        $statusList = array(
            self::STATUS_SUCCESS => Mage::helper('zip_payment')->__('success'),
            self::STATUS_WARNING => Mage::helper('zip_payment')->__('warning'),
            self::STATUS_ERROR => Mage::helper('zip_payment')->__('error')
        );

        return (!is_null($statusLevel) && isset($statusList[$statusLevel])) ? $statusList[$statusLevel] : null;
    }

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

        usort($this->result['items'], function($a, $b) {
            return $b['status'] - $a['status'];
        });

        Mage::app()->saveCache($this->result['overall_status'], self::HEALTH_CHECK_CACHE_ID);

        $this->addData($this->result);
        return $this->_toHtml();
    }

    protected function checkSystemHealth() {

        $sslEnabled = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        $curlEnabled = function_exists('curl_version');
        $privateKey = Mage::getStoreConfig(self::CONFIG_PRIVATE_KEY_PATH);
        $publicKey = Mage::getStoreConfig(self::CONFIG_PUBLIC_KEY_PATH);

        if(!$sslEnabled) {
            $this->addFailedItem(self::STATUS_WARNING, self::SSL_DISABLED_MESSAGE);
        }

        if(!$curlEnabled) {
            $this->addFailedItem(self::STATUS_ERROR, self::CURL_EXTENSION_DISABLED);
        }
        else {
            $curl = curl_init();
            $curlSSLVerificationEnabled = curl_getinfo($curl, CURLOPT_SSL_VERIFYPEER) && curl_getinfo($curl, CURLOPT_SSL_VERIFYPEER);

            $config = Mage::helper("zip_payment")->getAPIClientConfiguration();
            $url = $config->getHost();

            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_URL, $url);
            // https://www.blender.stackexchange.com/

            if(!$curlSSLVerificationEnabled) {

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);

                $this->addFailedItem(self::STATUS_WARNING, self::CURL_SSL_VERIFICATION_DISABLED_MESSAGE);
            }

            try {
                curl_exec($curl);

                $sslVerified = curl_getinfo($curl, CURLINFO_SSL_VERIFYRESULT) == 0;
                $isAccessible = !empty(curl_getinfo($curl, CURLINFO_PRIMARY_IP)); 
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);   

                if(!$sslVerified) {
                    $this->addFailedItem(self::STATUS_WARNING, self::API_CERTIFICATE_INVALID_MESSAGE);
                }

                if(!$isAccessible) {
                    $this->addFailedItem(self::STATUS_ERROR, self::API_INACCESSIBLE_MESSAGE);
                }

                if(empty($privateKey)) {
                    $this->addFailedItem(self::STATUS_ERROR, self::API_PRIVATE_KEY_INVALID_MESSAGE);
                }

                if(empty($publicKey)) {
                    $this->addFailedItem(self::STATUS_ERROR, self::API_PUBLIC_KEY_INVALID_MESSAGE);
                }

                if($httpCode == '401') {
                    //$this->addFailedItem(self::STATUS_ERROR, self::API_CREDENTIAL_INVALID_MESSAGE);
                }
                
            }
            catch(Exception $e) {
                $this->addFailedItem(self::STATUS_ERROR, self::API_INACCESSIBLE_MESSAGE);
            }
            
            curl_close($curl);
        }
        
    }


    protected function addFailedItem($status, $label) {

        if(!is_null($status) && $this->result['overall_status'] < $status) {
            $this->result['overall_status'] = $status;
        }

        $this->result['items'][] = array(
            "status" => $status,
            "label" => $label
        );

    }
}