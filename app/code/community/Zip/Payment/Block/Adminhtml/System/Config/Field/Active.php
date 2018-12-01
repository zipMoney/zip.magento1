<?php

class Zip_Payment_Block_Adminhtml_System_Config_Field_Active extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    const HEALTH_CHECK_CACHE_ID = 'zip_payment_health_check';

    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';
    const INACTIVE_VALUE = 0;

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $healthCheckResult = Mage::app()->loadCache(self::HEALTH_CHECK_CACHE_ID);

        if ($healthCheckResult == Zip_Payment_Block_Adminhtml_System_Config_Field_HealthCheck::STATUS_ERROR) {
            $element->setValue(self::INACTIVE_VALUE);
            $element->setDisabled('disabled');
            Mage::getModel('core/config')->saveConfig(self::CONFIG_ACTIVE_PATH, self::INACTIVE_VALUE);
        }
        else {
            $element->setDisabled(false);
        }

        return parent::_getElementHtml($element);

    }

}