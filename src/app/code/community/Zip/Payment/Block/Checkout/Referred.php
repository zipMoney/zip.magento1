<?php

/**
 * Block model of checkout Referred
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Checkout_Referred extends Zip_Payment_Block_Checkout_Page
{

    const CONFIG_CHECKOUT_REFERRED_HEADING_PATH = 'payment/zip_payment/checkout/referred/heading';
    const CONFIG_CHECKOUT_REFERRED_CONTENT_PATH = 'payment/zip_payment/checkout/referred/content';

    protected $_headingTextConfigPath = self::CONFIG_CHECKOUT_REFERRED_HEADING_PATH;
    protected $_contentHtmlConfigPath = self::CONFIG_CHECKOUT_REFERRED_CONTENT_PATH;
}
