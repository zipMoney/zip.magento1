<?php

class Zipmoney_ZipmoneyPayment_Model_Source_Product {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'zipmoney',
                'label' => Mage::helper('core')->__('zipMoney')
            ),
            array(
                'value' => 'zippay',
                'label' => Mage::helper('core')->__('zipPay')
            )
        );
    }

}
