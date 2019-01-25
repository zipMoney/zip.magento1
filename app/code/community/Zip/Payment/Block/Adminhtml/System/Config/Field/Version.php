<?php

/**
 * Block class of Admin version field
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Adminhtml_System_Config_Field_Version extends Zip_Payment_Block_Adminhtml_System_Config_Field
{
    /**
     * @var string
     */
    protected $template = 'zip/payment/system/config/field/version.phtml';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->addData(
            array(
                'version' => $this->getModelHelper()->getCurrentVersion()
            )
        );
        
        return $this->_toHtml();
    }
}