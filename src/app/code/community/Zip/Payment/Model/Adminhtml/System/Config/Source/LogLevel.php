<?php


/**
 * Configuration model for payment's log level
 *
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_LogLevel
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
                'label' => Mage::helper('zip_payment')->__('All')
            ),
            array(
                'value' => Zend_Log::INFO,
                'label' => Mage::helper('zip_payment')->__('Default')
            ),
            array(
                'value' => -1,
                'label' => Mage::helper('zip_payment')->__('None')
            )
        );
    }
}
