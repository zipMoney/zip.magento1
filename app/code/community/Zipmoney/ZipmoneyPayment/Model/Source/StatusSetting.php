<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Source_StatusSetting
{
    /**
     * Returns the order option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Sales_Model_Order::STATE_NEW,
                'label' => Mage::helper('core')->__('New')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                'label' => Mage::helper('core')->__('Pending Payment')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_PROCESSING,
                'label' => Mage::helper('core')->__('Processing')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_COMPLETE,
                'label' => Mage::helper('core')->__('Complete')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_CLOSED,
                'label' => Mage::helper('core')->__('Closed')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_CANCELED,
                'label' => Mage::helper('core')->__('Canceled')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_HOLDED,
                'label' => Mage::helper('core')->__('On Hold')
            ),
            array(
                'value' => Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                'label' => Mage::helper('core')->__('Payment Review')
            )
        );
    }
}
