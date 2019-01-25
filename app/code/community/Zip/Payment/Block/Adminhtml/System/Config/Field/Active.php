<?php

/**
 * Block class of Admin Active configuration field
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Adminhtml_System_Config_Field_Active extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    const INACTIVE_VALUE = 0;

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $healthCheckResult = Mage::app()->loadCache(Zip_Payment_Block_Adminhtml_System_Config_Field_HealthCheck::HEALTH_CHECK_CACHE_ID);

        // disable plugin if there any error been detected in health check
        if ($healthCheckResult == Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_ERROR) {
            $element->setValue(self::INACTIVE_VALUE);
            Mage::getModel('core/config')->saveConfig(Zip_Payment_Model_Config::CONFIG_ACTIVE_PATH, self::INACTIVE_VALUE);
        }
        
        return parent::_getElementHtml($element);

    }

}