<?php

class Zip_Payment_Model_Adminhtml_System_Config_Source_Currency extends Mage_Adminhtml_Model_System_Config_Source_Currency
{
    const CONFIG_SUPPORTED_CURRENCIES_PATH = 'payment/zip_payment/country_currency/supported_currencies';

    public function toOptionArray($isMultiselect)
    {
        $options = array();
        $supportedCurrencies = Mage::getStoreConfig(self::CONFIG_SUPPORTED_CURRENCIES_PATH);

        if(!empty($supportedCurrencies)) {

            $supportedCurrencies = explode(',', (string)$supportedCurrencies);
            $options = parent::toOptionArray($isMultiselect);

            $options = array_filter($options, function($option) use ($supportedCurrencies) {
                return in_array($option['value'], $supportedCurrencies);
            });
        }

        return $options;
        
    }

}
