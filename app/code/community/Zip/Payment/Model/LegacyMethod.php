<?php


class Zip_Payment_Model_LegacyMethod extends Zip_Payment_Model_Method
{

    public function isAvailable($quote = null)
    {
        return false;
    }

}