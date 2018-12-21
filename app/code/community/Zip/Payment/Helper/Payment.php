<?php

class Zip_Payment_Helper_Payment extends Mage_Payment_Helper_Data
{

    /**
     * Retrieve method model object, replace legacy zip payment with new zip payment
     *
     * @param   string $code
     * @return  Mage_Payment_Model_Method_Abstract|false
     */
    public function getMethodInstance($code)
    {
        $model = parent::getMethodInstance($code);

        if(!$model && $code == Zip_Payment_Model_Config::LEGACY_METHOD_CODE) {
            return parent::getMethodInstance(Zip_Payment_Model_Config::METHOD_CODE);
        }

        return $model;
    }

}