<?php

/** @var $installer Mage_Sales_Model_Resource_Setup */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');;
$installer->startSetup();

$installer->addAttribute('quote', 'zipmoney_cid', array(
    'label'     => 'zipMoney Checkout Id',
    'type'      => 'varchar',
    'required'  => false,
    'visible'   => false
));

$installer->addAttribute('order_payment', 'zipmoney_charge_id', array(
    'label'     => 'zipMoney Charge Id',
    'type'      => 'varchar',
    'required'  => false,
    'visible'   => false
));

$installer->endSetup();
