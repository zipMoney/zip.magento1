<?php

class Zipmoney_ZipmoneyPayment_Model_Source_Environment {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'sandbox',
                'label' => Mage::helper('core')->__('Sandbox')
            ),
            array(
                'value' => 'production',
                'label' => Mage::helper('core')->__('Live')
            )
        );
    }

}
