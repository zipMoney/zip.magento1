<?php

/**
 * TBlock template model
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Template extends Mage_Core_Block_Template {

    /**
     * Config model instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;

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