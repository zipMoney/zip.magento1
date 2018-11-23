<?php

class Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Deprecated extends Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Payment
{
    /**
     * Get was enabled config path
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getWasActiveConfigPath(Varien_Data_Form_Element_Abstract $element)
    {
        $groupConfig = $this->getGroup($element)->asArray();
        return isset($groupConfig['was_enabled_path']) ? $groupConfig['was_enabled_path'] : '';
    }

    /**
     * Check whether solution was enabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _wasActive(Varien_Data_Form_Element_Abstract $element)
    {
        $wasActiveConfigPath = $this->_getWasActiveConfigPath($element);
        return empty($wasActiveConfigPath)
            ? false
            : (bool)(string)$this->_getConfigDataModel()->getConfigDataValue($wasActiveConfigPath);
    }

    /**
     * Set solution as was enabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return Mage_Paypal_Block_Adminhtml_System_Config_Fieldset_Deprecated
     */
    protected function _setWasActive(Varien_Data_Form_Element_Abstract $element)
    {
        $wasActiveConfigPath = $this->_getWasActiveConfigPath($element);
        Mage::getConfig()->saveConfig($wasActiveConfigPath, 1);
        return $this;
    }

    /**
     * Get config data model
     *
     * @return Mage_Core_Model_Config
     */
    protected function _getConfigModel()
    {
        if (!$this->hasConfigModel()) {
            $this->setConfigModel(Mage::getConfig());
        }

        return $this->getConfigModel();
    }

    /**
     * Get all websites
     *
     * @return array
     */
    protected function _getWebsites()
    {
        if (!$this->hasWebsites()) {
            $this->setWebsites(Mage::app()->getWebsites());
        }

        return $this->getWebsites();
    }

    /**
     * Check whether current payment method is enabled on any scope
     *
     * @param string $activityPath
     * @return bool
     */
    public function isPaymentEnabledAnyScope($activityPath)
    {
        if ((bool)(string)$this->_getConfigModel()->getNode($activityPath, 'default')) {
            return true;
        }
        foreach ($this->_getWebsites() as $website) {
            if ((bool)(string)$this->_getConfigModel()->getNode($activityPath, 'website', (int)$website->getId())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Do not render solution if disabled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $isPaymentEnabled = $this->_isPaymentEnabled($element, array($this, 'isPaymentEnabledAnyScope'));
        if ($this->_wasActive($element) && $isPaymentEnabled) {
            return parent::render($element);
        }

        if ($isPaymentEnabled) {
            $this->_setWasActive($element);
            return parent::render($element);
        }

        return '';
    }
}
