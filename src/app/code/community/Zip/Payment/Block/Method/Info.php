<?php

/**
 * Block model of checkout method information
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Method_Info extends Mage_Payment_Block_Info
{
    const RECEIPT_NUMBER_LABEL = 'Receipt Number';

    protected $_template = 'zip/payment/method/info/default.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
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
            $info[self::RECEIPT_NUMBER_LABEL] = $additionalInformation[
                Zip_Payment_Model_Config::PAYMENT_RECEIPT_NUMBER_KEY
            ];
        }

        return $transport->addData($info);
    }

    /**
     * get log url
     */
    protected function getLogo()
    {
        return Mage::helper('zip_payment')->getConfig()->getLogo();
    }
}
