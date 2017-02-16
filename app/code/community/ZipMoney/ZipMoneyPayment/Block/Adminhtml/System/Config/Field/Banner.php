<?php
/**
 * @category  Aligent
 * @package   zipmoney
 * @author    Andi Han <andi@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Adminhtml_System_Config_Field_Banner extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_frontendExperienceEnabled  = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_getFrontendExperienceEnabled();
    }

    protected function _getFrontendExperienceEnabled()
    {
        if ($this->_frontendExperienceEnabled === null) {
            $vPath = Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_MARKETING_BANNERS_ACTIVE;
            $this->_frontendExperienceEnabled = Mage::getSingleton('adminhtml/config_data')->getConfigDataValue($vPath);
        }
        return $this->_frontendExperienceEnabled;
    }


    /**
     * Override Mage_Adminhtml_Block_System_Config_Form_Field::_getElementHtml
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $vJsString = '';
        $vFunctionBody = '';
        $vInitFunctionBody = '';
        $vElementId = $element->getHtmlId();
        switch ($vElementId) {
            case 'payment_zipmoney_marketing_banners_banner_active':
                $vFunctionBody = 'frontendExperienceController.onEnabledChange(this.value)';
                $vInitFunctionBody = $this->__getInitConfigJs();
                break;
            default:
                // should never go to here
                break;
        }

        if ($vFunctionBody) {
            $vJsString = '
                $("' . $vElementId . '").observe("change", function () {
                    ' . $vFunctionBody . '
                });
            ';
        }

        if ($vInitFunctionBody) {
            $vJsString = $vJsString . $vInitFunctionBody;
        }
        if ($vJsString) {
            $vHtml = parent::_getElementHtml($element) . $this->helper('adminhtml/js')
                    ->getScript('document.observe("dom:loaded", function() {' . $vJsString . '});');
        } else {
            $vHtml = parent::_getElementHtml($element);
        }

        return $vHtml;
    }

    /**
     * Get Js used to initialise the fields based
     *
     * @return string
     */
    private function __getInitConfigJs()
    {
        $vInitFunctionBody = 'frontendExperienceController.initConfig(' . $this->_getFrontendExperienceEnabled() . ');';
        return $vInitFunctionBody;
    }
}