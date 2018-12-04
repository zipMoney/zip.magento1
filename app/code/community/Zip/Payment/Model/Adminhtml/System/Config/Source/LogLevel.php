<?php

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
