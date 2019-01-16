<?php

/**
 * Block model for checkout overlay
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Checkout_Overlay extends Mage_Core_Block_Template
{
    const CONFIG_CHECKOUT_LOADER_IMAGE_PATH = 'payment/zip_payment/checkout/loader_image';

    /**
     * @var Zip_Payment_Model_Config
     */
    protected $config;

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
    public function getModelHelper()
    {
        return Mage::helper('zip_payment');
    }

    /**
     * is current payment active
     * 
     * @return boolean
     */
    public function isActive() {

        return $this->getModelHelper()->isActive();
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
     * get loader images
     * 
     * @return string
     */
    public function getLoaderImageUrl() {
        return $this->getConfig()->getValue(self::CONFIG_CHECKOUT_LOADER_IMAGE_PATH);
    }

}