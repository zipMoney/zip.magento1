<?php


/**
 * Configuration model for supported countries
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_Country extends Mage_Adminhtml_Model_System_Config_Source_Country
{

    public function toOptionArray($isMultiselect = false)
    {
        $options = array();
        $supportedCountries = Mage::helper('zip_payment')
            ->getConfig()
            ->getValue(Zip_Payment_Model_Config::CONFIG_SUPPORTED_COUNTRIES_PATH);

        if (!empty($supportedCountries)) {
            $supportedCountries = explode(',', (string) $supportedCountries);
            $options = parent::toOptionArray($isMultiselect);

            $options = array_filter(
                $options, function ($option) use ($supportedCountries) {
                    return in_array($option['value'], $supportedCountries);
                }
            );
        }

        return $options;

    }
}
