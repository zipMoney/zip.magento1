<?php

class Zip_Payment_Model_Adminhtml_Observer
{

    public function loadConfig(Varien_Event_Observer $observer)
    {
        $paymentGroups = $observer->getEvent()->getConfig()->getNode('sections/payment/groups');

        $payments = $paymentGroups->xpath('zip_payment/*');
        
        foreach ($payments as $payment) {
            $fields = $paymentGroups->xpath((string)$payment->group . '/fields');

            if (isset($fields[0])) {
                $fields[0]->appendChild($payment, true);
            }
        }
    }

}
