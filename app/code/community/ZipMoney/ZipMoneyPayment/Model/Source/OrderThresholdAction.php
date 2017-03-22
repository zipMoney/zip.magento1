<?php

class Zipmoney_ZipmoneyPayment_Model_Source_OrderThresholdAction {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'hide',
                'label' => Mage::helper('core')->__('Hide Payment Option')
            ),
            array(
                'value' => 'display_notice',
                'label' => Mage::helper('core')->__('Display Notice')
            )
        );
    }

}
