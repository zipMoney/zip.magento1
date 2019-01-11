<?php

/**
 * Block model of cms page for Zip Payment
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Checkout_Page extends Mage_Core_Block_Template
{  

    protected $headingTextConfigPath = null;
    protected $contentHtmlConfigPath = null;

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


    /**
     * get Zip payment logo
     * @return string
     */
    public function getLogo() {
        return $this->getConfig()->getLogo();
    }

    /**
     * get Zip payment slogan
     * @return string
     */
    public function getSlogan() {
        return $this->getConfig()->getTitle();
    }


    /**
     * retrieve all message items
     * @return array
     */
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

     /**
     * get heading text
     * @return string
     */
    public function getHeadingText()
    {
        return $this->getHelper()->__($this->getConfig()->getValue($this->headingTextConfigPath));
    }

    /**
     * get content html
     */
    public function getContentHtml()
    {
        return $this->getHelper()->__($this->getConfig()->getValue($this->contentHtmlConfigPath));
    }

}