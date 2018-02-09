<?php
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */



/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

/**
 * adds zipmoney statuses to sales_order_status table
 */
$data = array(
    array('zip_authorised', 'zipMoney Authorised')
//    array('zip_declined', 'zipMoney Payment Declined')
);

/**
 * check if status exists already
 */
$aNew = array();
foreach ($data as $status) {
    $oStatusModel = Mage::getModel('sales/order_status')->load($status[0]);
    if (!$oStatusModel || !$oStatusModel->getId()) {
        $aNew[] = $status;
    }
}

if (count($aNew) > 0) {
    $connection = $installer->getConnection()->insertArray(
        $installer->getTable('sales/order_status'),
        array('status', 'label'),
        $aNew
    );
}


/**
 * adds zipmoney statuses to sales_order_status_state table
 */
$data = array(
    array('zip_authorised', 'pending_payment', 0)
);

/**
 * check if status exists already
 */
$aNew = array();
foreach ($data as $status) {
    $select = $installer->getConnection()->select()
        ->from(array('e' => $installer->getTable('sales/order_status_state')))
        ->where("e.status=?", $status[0]);
    $result = $installer->getConnection()->fetchAll($select);
    if (!$result) {
        $aNew[] = $status;
    }
}

if (count($aNew) > 0) {
    $connection = $installer->getConnection()->insertArray(
        $installer->getTable('sales/order_status_state'),
        array('status', 'state', 'is_default'),
        $aNew
    );
}

$installer->endSetup();