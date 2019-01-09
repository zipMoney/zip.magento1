<?php

/**
 * Block model of checkout method information
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Method_Info extends Mage_Payment_Block_Info
{
    const RECEIPT_NUMBER_LABEL = 'Receipt Number';

    protected $template = 'zip/payment/method/info/default.phtml';

    /**
     * Config model instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->template);
    }

    /**
     * put receipt number into additional information
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $additionalInformation = $payment->getAdditionalInformation();

        $info = array();

        if (isset($additionalInformation[Zip_Payment_Model_Config::PAYMENT_RECEIPT_NUMBER_KEY])) {
            $info[self::RECEIPT_NUMBER_LABEL] = $additionalInformation[Zip_Payment_Model_Config::PAYMENT_RECEIPT_NUMBER_KEY];
        }

        return $transport->addData($info);
    }

    /**
     * Config instance getter
     * @return Zip_Payment_Model_Config
     */
    public function getConfig()
    {
        if ($this->config == null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }
        return $this->config;
    }

    protected function getLogo() {
        return $this->getConfig()->getLogo();
    }
}
