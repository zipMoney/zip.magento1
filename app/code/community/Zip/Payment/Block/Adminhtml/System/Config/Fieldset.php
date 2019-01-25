<?php


class Zip_Payment_Block_Adminhtml_System_Config_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {


    /**
     * Config model instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;
    protected $template = null;

    /**
     * get config instance
     */
    protected function getConfig() {
        if($this->config == null) {
            $this->config = $this->getModelHelper()->getConfig();
        }
        return $this->config;
    }


    /**
     * get model helper
     */
    protected function getModelHelper() {
        return Mage::helper('zip_payment');
    }

}