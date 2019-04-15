<?php

/**
 * Block model of checkout failure
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Checkout_Failure extends Zip_Payment_Block_Checkout_Page
{

    const CONFIG_CHECKOUT_FAILURE_HEADING_PATH = 'payment/zip_payment/checkout/failure/heading';
    const CONFIG_CHECKOUT_FAILURE_CONTENT_PATH = 'payment/zip_payment/checkout/failure/content';

    protected $_headingTextConfigPath = self::CONFIG_CHECKOUT_FAILURE_HEADING_PATH;
    protected $_contentHtmlConfigPath = self::CONFIG_CHECKOUT_FAILURE_CONTENT_PATH;

}
