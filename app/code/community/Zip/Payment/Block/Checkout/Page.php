<?php

/**
 * Block model of cms page for Zip Payment
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Checkout_Page extends Mage_Core_Block_Template
{

    protected $_headingTextConfigPath = null;
    protected $_contentHtmlConfigPath = null;
    protected $_messageItems = null;

    public function __construct()
    {
        $this->_messageItems = Mage::helper('zip_payment')->getCheckoutSession()->getMessages()->getItems();
    }

    /**
     * get Zip payment logo
     *
     * @return string
     */
    public function getLogo()
    {
        return Mage::helper('zip_payment')->getConfig()->getLogo();
    }

    /**
     * get Zip payment slogan
     *
     * @return string
     */
    public function getSlogan()
    {
        return Mage::helper('zip_payment')->getConfig()->getTitle();
    }


    /**
     * retrieve all message items
     *
     * @return array
     */
    public function get_messageItems()
    {
        return $this->_messageItems;
    }

     /**
      * get heading text
      *
      * @return string
      */
    public function getHeadingText()
    {
        $headingText = Mage::helper('zip_payment')->getConfig()->getValue($this->_headingTextConfigPath);
        return Mage::helper('zip_payment')->__($headingText);
    }

    /**
     * get content html
     */
    public function getContentHtml()
    {
        return Mage::helper('zip_payment')->getConfig()->getValue($this->_contentHtmlConfigPath);
    }

}
