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
     * Config model instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;

    /**
     * get config instance
     */
    protected function getConfig() {
        if($this->config == null) {
            $this->config = $this->getHelper()->getConfig();
        }
        return $this->config;
    }


    /**
     * get model helper
     */
    protected function getHelper() {
        return Mage::helper('zip_payment');
    }

    /**
     * Check admin notifications
     */
    public function checkAdminNotifications(Varien_Event_Observer $observer) {

        $enabled = $this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_NOTIFICATION_ENABLED_PATH);
        
        if($enabled) {
            Mage::getSingleton('zip_payment/adminhtml_notification_feed')->checkUpdate();
        }
    }

    /**
     * Load admin config dynamically
     */
    public function loadConfig(Varien_Event_Observer $observer)
    {
        $paymentGroups = $observer->getEvent()->getConfig()->getNode('sections/payment/groups');

        $payments = $paymentGroups->xpath('zip_payment_solution/*');
        
        foreach ($payments as $payment) {
            $fields = $paymentGroups->xpath((string)$payment->group . '/fields');

            if (isset($fields[0])) {
                $fields[0]->appendChild($payment, true);
            }
        }
    }

}
