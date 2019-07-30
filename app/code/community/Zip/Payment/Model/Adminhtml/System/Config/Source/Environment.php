<?php

/**
 * Configuration model for environment
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_Environment
{
    /**
     * Returns the environment option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'sandbox',
                'label' => Mage::helper('zip_payment')->__('Sandbox')
            ),
            array(
                'value' => 'production',
                'label' => Mage::helper('zip_payment')->__('Production')
            )
        );
    }

}
