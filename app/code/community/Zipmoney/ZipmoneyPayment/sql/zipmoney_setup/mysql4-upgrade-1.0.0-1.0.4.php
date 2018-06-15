<?php
/**
 * @author    roger.bi@zipmoney.com.au
 */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');;
$installer->startSetup();
try {
    Mage::getConfig()->saveConfig('payment/zipmoneypayment/title', 'Zip - Own it now, Pay later', 'default', 0);
}
catch (Exception $e) {
   Mage::log($e->getMessage(),null,"Zipmoney_update_title_error",true);
}
$installer->endSetup();

