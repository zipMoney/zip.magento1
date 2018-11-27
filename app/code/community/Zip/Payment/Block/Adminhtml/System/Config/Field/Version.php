<?php

class Zip_Payment_Block_Adminhtml_System_Config_Field_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return "<strong>" . Mage::helper("zip_payment")->getExtensionVersion() . "</strong>";
    }
}