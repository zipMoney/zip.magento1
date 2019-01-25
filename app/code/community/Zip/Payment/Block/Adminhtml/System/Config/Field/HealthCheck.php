<?php

/**
 * Block class of Admin health check field
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Adminhtml_System_Config_Field_HealthCheck extends Zip_Payment_Block_Adminhtml_System_Config_Field
{
    /**
     * @var string
     */
    protected $template = 'zip/payment/system/config/field/health_check.phtml';
    const HEALTH_CHECK_CACHE_ID = 'zip_payment_health_check';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $result = $element->getValue();
        Mage::app()->saveCache($result['overall_status'], self::HEALTH_CHECK_CACHE_ID);
        $this->addData($result);

        return $this->_toHtml();
    }

    public function getStatusLabel($statusLevel = null)
    {
        $statusList = array(
            Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_SUCCESS => Mage::helper('zip_payment')->__('Success'),
            Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_WARNING => Mage::helper('zip_payment')->__('Warning'),
            Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_ERROR => Mage::helper('zip_payment')->__('Error')
        );

        return (!is_null($statusLevel) && isset($statusList[$statusLevel])) ? $statusList[$statusLevel] : null;
    }


    
}