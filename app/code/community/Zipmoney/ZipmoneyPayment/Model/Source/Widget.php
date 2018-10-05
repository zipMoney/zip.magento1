<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Source_Widget
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
