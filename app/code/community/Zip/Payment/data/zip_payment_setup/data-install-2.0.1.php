<?php

/**
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

// add block permission for all zip payment blocks
if (Mage::getModel('admin/block')) {
    $blocks = array(
        'zip_payment/widget',
        'zip_payment/checkout_overlay',
        'zip_payment/checkout_script',
        'zip_payment/checkout_failure',
        'zip_payment/checkout_referred'
    );

    foreach ($blocks as $block) {
        Mage::getModel('admin/block')->load($block, 'block_name')
            ->setData('block_name', $block)
            ->setData('is_allowed', 1)
            ->save();
    }
}

// Delete old log file
Mage::helper('zip_payment')->removeLogFile('zipMoney-payment.log');
