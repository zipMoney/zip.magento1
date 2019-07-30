<?php

/**
 * Block class of Admin health check field
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Adminhtml_System_Config_Field_HealthCheck
extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var string
     */
    protected $_template = 'zip/payment/system/config/field/health_check.phtml';
    const HEALTH_CHECK_CACHE_ID = 'zip_payment_health_check';

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate() && $this->_template) {
            $this->setTemplate($this->_template);
        }

        return $this;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $result = $element->getValue();
        Mage::app()->saveCache($result['overall_status'], self::HEALTH_CHECK_CACHE_ID);
        $this->addData($result);

        return $this->_toHtml();
    }

    public function getStatusLabel($statusLevel = null)
    {
        $helper = Mage::helper('zip_payment');

        $statusList = array(
            Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_SUCCESS => $helper->__('Success'),
            Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_WARNING => $helper->__('Warning'),
            Zip_Payment_Model_Adminhtml_System_Config_Backend_HealthCheck::STATUS_ERROR => $helper->__('Error')
        );

        return ($statusLevel !== null && isset($statusList[$statusLevel])) ? $statusList[$statusLevel] : null;
    }



}
