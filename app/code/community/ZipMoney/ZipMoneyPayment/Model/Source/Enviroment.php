<?php

class Zipmoney_ZipmoneyPayment_Model_Source_Enviroment {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'sandbox',
                'label' => Mage::helper('core')->__('Test')
            ),
            array(
                'value' => 'production',
                'label' => Mage::helper('core')->__('Live')
            )
        );
    }

}
