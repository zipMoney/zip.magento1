<?php


class Zip_Payment_Block_Standard_Info extends Mage_Payment_Block_Info
{

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
