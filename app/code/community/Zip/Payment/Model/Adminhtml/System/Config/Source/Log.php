<?php
/**
 * @category  Zipmoney
 * @package   Zip_Payment
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zip_Payment_Model_Adminhtml_System_Config_Source_Log
{
    /**
     * Returns the log settings option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Zend_Log::DEBUG,
                'label' => Mage::helper('core')->__('All')
            ),
            array(
                'value' => Zend_Log::INFO,
                'label' => Mage::helper('core')->__('Default')
            ),
            array(
                'value' => -1,
                'label' => Mage::helper('core')->__('None')
            )
        );
    }
}
