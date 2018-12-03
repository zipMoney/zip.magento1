<?php

class Zip_Payment_Model_Adminhtml_System_Config_Source_Mode
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
