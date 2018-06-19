<?php
/**
 * @author    roger.bi@zipmoney.com.au
 * genaral update script for define update array for normal use and modify file name as well
 */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->startSetup();

try {
    $table = $this->getTable('core/config_data');

    $updates = array(
        array(
            'cond' => array('path' => 'payment/zipmoneypayment/title'),
            'data' => array('value' => 'Zip - Own it now, Pay later'),
        ),

    );

    foreach($updates as $update) {
        $cond = array();

        foreach($update['cond'] as $field => $value) {
            $cond[] = $this->getConnection()->quoteInto($field . '=?', $value);
        }
        try {
            $this->getConnection()->update($table, $update['data'], implode(' AND ', $cond));
        }
        catch (Exception $e) {
            Mage::log($e->getMessage(),null,"Zipmoney_update_title_error",true);
        }
    }
}
catch (Exception $e) {
    Mage::log($e->getMessage(),null,"Zipmoney_update_title_error",true);
    throw $e;
}
$installer->endSetup();






















