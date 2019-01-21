<?php


class Zip_Payment_Block_Adminhtml_System_Config_Field extends Mage_Adminhtml_Block_System_Config_Form_Field {


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

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate() && $this->template) {
            $this->setTemplate($this->template);
        }
        return $this;
    }




}