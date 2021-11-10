<?php
/**
 * Configuration model for Region
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_Region
{
    protected $_availableCountries = array("au","gb","mx","nz","ca","us","ae","sg","za");
    /**
     * Returns the region option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $countries = Mage::getResourceModel('directory/country_collection')->loadData()->toOptionArray(false);
        $countryList = array();
        foreach ($countries as $country) {
            $countryCode = strtolower($country['value']);
            if (in_array($countryCode, $this->_availableCountries)) {
                $countryList[] = array (
                    'value' => $countryCode,
                    'label' => Mage::helper('zip_payment')->__($country['label'])
                );
            }
        }

        return $countryList;
    }
}
