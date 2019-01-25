<?php

/**
 * Block model for checkout overlay
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Checkout_Overlay extends Zip_Payment_Block_Template
{
    const CONFIG_CHECKOUT_LOADER_IMAGE_PATH = 'payment/zip_payment/checkout/loader_image';

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