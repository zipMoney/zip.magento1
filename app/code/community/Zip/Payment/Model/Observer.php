<?php

class Zip_Payment_Model_Observer
{

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
