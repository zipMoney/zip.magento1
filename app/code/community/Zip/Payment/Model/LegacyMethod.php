<?php

/**
 * Handle Legacy zip payment method
 * Extends new Zip payment method
 *
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Model_LegacyMethod extends Zip_Payment_Model_Method
{

    /**
     * force legacy payment to be invalid
     */
    public function isAvailable($quote = null)
    {
        return false;
    }

}