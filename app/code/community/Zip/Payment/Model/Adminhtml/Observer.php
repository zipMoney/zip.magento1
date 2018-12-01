<?php

class Zip_Payment_Model_Adminhtml_Observer
{
    const CONFIG_NOTIFICATION_ENABLED_PATH = 'payment/zip_payment/admin_notification/enabled';

    public function checkNotifications(Varien_Event_Observer $observer) {
        
        if(Mage::getStoreConfigFlag(self::CONFIG_NOTIFICATION_ENABLED_PATH)) {
            Mage::getSingleton('zip_payment/adminhtml_notification_feed')->checkUpdate();
        }
    }

    
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
