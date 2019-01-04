<?php


class Zip_Payment_Model_Legacy extends Zip_Payment_Model_Method
{

    public function isAvailable($quote = null)
    {
        return false;
    }

}