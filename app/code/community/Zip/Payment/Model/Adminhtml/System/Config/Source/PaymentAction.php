<?php

class Zip_Payment_Model_Adminhtml_System_Config_Source_PaymentAction
{
    /**
     * Returns the payment action option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Zip_Payment_Model_Method::ACTION_AUTHORIZE,
                'label' => Mage::helper('zip_payment')->__('Authorize Only')
            ),
            array(
                'value' => Zip_Payment_Model_Method::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('zip_payment')->__('Authorize and Capture')
            )
        );
    }

}
