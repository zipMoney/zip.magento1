<?php

class Zip_Payment_Block_Checkout_Error extends Mage_Core_Block_Template
{  

    protected $config = null;
    protected $messageItems = null;

    public function __construct()
    {
        Mage::helper('zip_payment')->getCheckoutSession()->addError(Mage::helper('zip_payment')->__('Checkout has been rejected'));
Mage::helper('zip_payment')->getCheckoutSession()->addError(Mage::helper('zip_payment')->__('Your application is currently under review by zipMoney and will be processed very shortly.You can contact the customer care at customercare@zipmoney.com.au for any enquiries'));


        $this->messageItems = $this->getHelper()->getCheckoutSession()->getMessages()->getItems();
    }

    protected function getConfig() {
        if($this->config == null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }
        return $this->config;
    }

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('zip_payment');
    }


    public function getLogo() {
        return $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_LOGO_PATH);
    }

    public function getTitle() {
        return $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_TITLE_PATH);
    }

    public function getHeadingText()
    {
        if(!empty($this->messageItems)) {
            return (string) $this->messageItems[0]->getText();
        }

        return $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_GENERAL_ERROR_PATH));
    }

    public function getMessageItems()
    {
        if(!empty($this->messageItems)) {
            array_shift($this->messageItems);

            if(!empty($this->messageItems)) {
                return $this->messageItems;
            }
        }

        return null;
    }
}