<?php

/**
 * Block model for checkout overlay
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Checkout_Overlay extends Mage_Core_Block_Template
{
    /**
     * is current payment active
     *
     * @return boolean
     */
    public function isActive()
    {
        return Mage::helper('zip_payment')->isActive();
    }
}
