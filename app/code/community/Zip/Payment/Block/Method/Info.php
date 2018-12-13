<?php


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

    protected function getConfig() {
        if($this->config == null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }

        return $this->config;
    }

    protected function getLogo() {
        return $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_LOGO_PATH);
    }
}
