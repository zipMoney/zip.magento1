<?php

/**
 * PayPal common payment info block
 * Uses default templates
 */
class Zipmoney_ZipmoneyPayment_Block_Standard_Info extends Mage_Payment_Block_Info
{

    /**
     * Prepare PayPal-specific payment information
     *
     * @param Varien_Object|array $transport
     * return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $additional_information = $payment->getAdditionalInformation();
        $info = array();

        if (isset($additional_information['receipt_number'])) {
            $info['Receipt Id'] = $additional_information['receipt_number'];
        }

        return $transport->addData($info);
    }
}
