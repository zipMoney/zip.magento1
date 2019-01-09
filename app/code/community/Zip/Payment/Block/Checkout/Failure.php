<?php

/**
 * Block model of checkout failure
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Checkout_Failure extends Mage_Core_Block_Template
{  

    /**
     * @var Zip_Payment_Model_Config
     */
    protected $config;
    protected $messageItems = null;

    public function __construct()
    {
        $this->messageItems = $this->getHelper()->getCheckoutSession()->getMessages()->getItems();
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
        return $this->getConfig()->getLogo()
    }

    public function getTitle() {
        return  $this->getConfig()->getTitle();
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


    public function getContactText()
    {
        return $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_ERROR_CONTACT_PATH));
    }
}