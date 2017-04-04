<?php

class Zipmoney_ZipmoneyPayment_Model_Source_Widget {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'enabled',
                'label' => Mage::helper('core')->__('Yes')
            ),
            array(
                'value' => 'disable',
                'label' => Mage::helper('core')->__('No')
            )
        );
    }

}
