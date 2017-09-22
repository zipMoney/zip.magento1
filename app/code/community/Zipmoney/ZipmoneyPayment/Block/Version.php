<?php

class Zipmoney_ZipmoneyPayment_Block_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return "<strong>".Mage::helper("zipmoneypayment")->getExtensionVersion(). "</strong>";
    }
}