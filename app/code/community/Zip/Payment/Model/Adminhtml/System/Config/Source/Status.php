<?php


class Zip_Payment_Model_Adminhtml_System_Config_Source_Status
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
