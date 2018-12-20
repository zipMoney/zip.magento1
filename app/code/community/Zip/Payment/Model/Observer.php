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

    public function setResponseAfterSavePayment(Varien_Event_Observer $observer) {

        $methodCode = Mage::helper('zip_payment')->getCurrentPaymentMethod();

        if($methodCode == Zip_Payment_Model_Config::METHOD_CODE) {
            
            $controller = $observer->getEvent()->getData('controller_action');

            $result = Mage::helper('core')->jsonDecode(
                $controller->getResponse()->getBody('default'),
                Zend_Json::TYPE_ARRAY
            );

            if (empty($result['error'])) {

                $controller->loadLayout('checkout_onepage_review');
                $html = $controller->getLayout()->getBlock('root')->toHtml();

                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $html
                );
                $result['success'] = false;

                if(empty($result['redirect'])) {
                    $result['error'] = 'Something wrong while creating checkout via Zip Payment ';
                }

                $controller->getResponse()->clearHeader('Location');
                $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }

        }

        return $this;

    }

    
}
