<?php

class Zip_Payment_Block_Adminhtml_System_Config_Field_CredentialButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $template = 'zip/payment/system/config/field/credential_button.phtml';

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

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $elementHtmlId = $element->getHtmlId();

        $this->addData(
            array(
                'button_label' => Mage::helper('zip_payment')->__($originalData['button_label']),
                'production_url' => $originalData['production_url'],
                'sandbox_url' => $originalData['sandbox_url'],
                'html_id' => $elementHtmlId,
            )
        );
        return $this->_toHtml();
    }

}
