<?php

class Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Wizard extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $header_title_template = 'zip/payment/system/config/fieldset/wizard.phtml';

    /**
     * Add custom css class
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' with-button '
            . ($this->isPaymentEnabled($element) ? 'enabled' : '');
    }

    /**
     * Check whether current payment method is enabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param callback|null $configCallback
     * @return bool
     */
    protected function isPaymentEnabled($element, $configCallback = null)
    {
        $groupConfig = $this->getGroup($element)->asArray();
        $activePath = isset($groupConfig['active_path']) ? $groupConfig['active_path'] : '';
        
        return !empty($activePath) ? (bool)(string)$this->_getConfigDataModel()->getConfigDataValue($activePath) : false;
    }

    /**
     * Get config data model
     *
     * @return Mage_Adminhtml_Model_Config_Data
     */
    protected function _getConfigDataModel()
    {
        if (!$this->hasConfigDataModel()) {
            $this->setConfigDataModel(Mage::getSingleton('adminhtml/config_data'));
        }

        return $this->getConfigDataModel();
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->header_title_template);
        $block->setData(array(
            'element' => $element,
            'config' => $this->getGroup($element)->asArray()
        ));

        return $block->toHtml();
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _getCollapseState($element)
    {
        return false;
    }
}
