<?php
/**
 * @category  Zipmoney
 * @package   Zip_Payment
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zip_Payment_Model_Adminhtml_System_Config_Source_Widget
{
    /**
     * Returns the widget option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'enabled',
                'label' => Mage::helper('core')->__('Yes')
            ),
            array(
                'value' => 'disable',
                'label' => Mage::helper('core')->__('No')
            )
        );
    }

}
