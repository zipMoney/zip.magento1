<?php

class Zip_Payment_Model_Adminhtml_System_Config_Source_Country extends Mage_Adminhtml_Model_System_Config_Source_Country
{
    
    public function toOptionArray($isMultiselect = false)
    {
        $options = array();
        $supportedCountries = Mage::getSingleton('zip_payment/config')->getValue(Zip_Payment_Model_Config::CONFIG_SUPPORTED_COUNTRIES_PATH);

        if(!empty($supportedCountries)) {

            $supportedCountries = explode(',', (string)$supportedCountries);
            $options = parent::toOptionArray($isMultiselect);

            $options = array_filter($options, function($option) use ($supportedCountries) {
                return in_array($option['value'], $supportedCountries);
            });
        }

        return $options;
        
    }
}
