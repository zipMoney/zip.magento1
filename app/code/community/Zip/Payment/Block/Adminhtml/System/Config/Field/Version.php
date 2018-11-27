<?php

class Zip_Payment_Block_Adminhtml_System_Config_Field_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $template = 'zip/payment/system/config/field/version.phtml';

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->template);
        }
        return $this;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->addData(
            array(
                'version' => Mage::helper("zip_payment")->getExtensionVersion()
            )
        );
        
        return $this->_toHtml();
    }
}