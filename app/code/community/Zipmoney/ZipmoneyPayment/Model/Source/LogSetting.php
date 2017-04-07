<?php

class Zipmoney_ZipmoneyPayment_Model_Source_LogSetting {
    public function toOptionArray() {
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
