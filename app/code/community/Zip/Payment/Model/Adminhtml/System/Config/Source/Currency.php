<?php

/**
 * Configuration model for payment specific currencies                                                                                          
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_Currency extends Mage_Adminhtml_Model_System_Config_Source_Currency
{

    public function toOptionArray($isMultiselect)
    {
        $options = array();
        $supportedCurrencies = Mage::getSingleton('zip_payment/config')->getValue(Zip_Payment_Model_Config::CONFIG_SUPPORTED_CURRENCIES_PATH);

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
