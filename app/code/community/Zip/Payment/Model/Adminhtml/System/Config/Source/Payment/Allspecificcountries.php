<?php

/**
 * Configuration model for payment specific countries                                                                                          
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Model_Adminhtml_System_Config_Source_Payment_Allspecificcountries extends Mage_Adminhtml_Model_System_Config_Source_Payment_Allspecificcountries
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 1, 
                'label' => Mage::helper('adminhtml')->__('Specific Countries')
            )
        );
    }
}
