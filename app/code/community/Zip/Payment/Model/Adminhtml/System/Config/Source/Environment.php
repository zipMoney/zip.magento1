<?php
/**
 * @category  Zipmoney
 * @package   Zip_Payment
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zip_Payment_Model_Adminhtml_System_Config_Source_Environment
{
    /**
     * Returns the environment option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'sandbox',
                'label' => Mage::helper('core')->__('Sandbox')
            ),
            array(
                'value' => 'production',
                'label' => Mage::helper('core')->__('Live')
            )
        );
    }

}
