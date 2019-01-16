<?php


/**
 * Observer model for Admin                                                                                      
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/


class Zip_Payment_Model_Adminhtml_Observer
{

    /**
     * Check admin notifications
     */
    public function checkAdminNotifications(Varien_Event_Observer $observer) {

        $enabled = Mage::getSingleton('zip_payment/config')->getFlag(Zip_Payment_Model_Config::CONFIG_NOTIFICATION_ENABLED_PATH);
        
        if($enabled) {
            Mage::getSingleton('zip_payment/adminhtml_notification_feed')->checkUpdate();
        }
    }

    /**
     * enabled / disable landing page based on configuration
     */
    public function updateLandingPageStatus(Varien_Event_Observer $observer) {

        $isLandingPageEnabled = Mage::getSingleton('zip_payment/config')->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);
        $identifier = Zip_Payment_Model_Config::LANDING_PAGE_URL_IDENTIFIER;
        Mage::getSingleton('cms/page')->load($identifier, 'identifier')->setData('is_active', $isLandingPageEnabled ? 1 : 0)->save();
    }


    /**
     * Load admin config dynamically
     */
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
