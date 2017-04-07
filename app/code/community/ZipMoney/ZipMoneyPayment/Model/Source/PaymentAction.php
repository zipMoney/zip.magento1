<?php

class Zipmoney_ZipmoneyPayment_Model_Source_PaymentAction {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'authorise',
                'label' => Mage::helper('core')->__('Authorise')
            ),
            array(
                'value' => 'capture',
                'label' => Mage::helper('core')->__('Capture')
            )
        );
    }

}
