<?php
/**
 * Configuration model for Region
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_Region
{
    /**
     * Returns the region option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'au',
                'label' => Mage::helper('zip_payment')->__('Australia')
            ),
            array(
                'value' => 'nz',
                'label' => Mage::helper('zip_payment')->__('New Zealand')
            ),
            array(
                'value' => 'gb',
                'label' => Mage::helper('zip_payment')->__('United Kingdom')
            ),
            array(
                'value' => 'us',
                'label' => Mage::helper('zip_payment')->__('United States')
            ),
            array(
                'value' => 'za',
                'label' => Mage::helper('zip_payment')->__('South Africa')
            ),
            array(
                'value' => 'mx',
                'label' => Mage::helper('zip_payment')->__('Mexico')
            ),
            array(
                'value' => 'ae',
                'label' => Mage::helper('zip_payment')->__('United Arab Emirates')
            ),
            array(
                'value' => 'ca',
                'label' => Mage::helper('zip_payment')->__('Canada')
            ),
        );
    }

}
