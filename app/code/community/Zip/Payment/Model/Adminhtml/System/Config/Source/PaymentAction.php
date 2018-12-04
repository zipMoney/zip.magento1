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
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                'label' => Mage::helper('core')->__('Authorize Only')
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('core')->__('Authorize and Capture')
            )
        );
    }

}
