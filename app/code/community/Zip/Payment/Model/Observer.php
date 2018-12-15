<?php

class Zip_Payment_Model_Observer
{

    /**
     * disable full page cache for all actions in Zip_Payment_CheckoutController
     */
    public function processControllerPreDispatch(Varien_Event_Observer $observer) {

        $action = $observer->getEvent()->getControllerAction();

        // Check to see if $action is a Zip Payment Checkout controller
        if ($action instanceof Zip_Payment_CheckoutController) {
            $cache = Mage::app()->getCacheInstance();
            // Tell Magento to 'ban' the use of FPC for this request
            $cache->banUse('full_page');
        }

    }

    public function startPlacePayment(Varien_Event_Observer $observer) {

        $payment = $observer->getEvent()->getPayment();
        $method = $payment->getMethodInstance();

        if($method->getCode() == Zip_Payment_Model_Config::METHOD_CODE) {

            $redirectUrl = $method->getOrderPlaceRedirectUrl();

            if(!empty($redirectUrl)) {

                $controller = $observer->getEvent()->getData('controller_action');

                $response = array(
                    'redirect' => $redirectUrl
                );
    
                echo Mage::helper('core')->jsonEncode($response);
                exit;

            }

        }

        return $this;
       
    }

}
