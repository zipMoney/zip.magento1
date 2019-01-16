<?php

/**
 * Checkout controller
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_CheckoutController extends Zip_Payment_Controller_Checkout
{

    /**
     * Handling response from API response
     *
     * @throws Mage_Core_Exception
     */
    public function responseAction()
    {
        if(!$this->getHelper()->isActive()) {
            return;
        }

        $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - responseAction'));
        $this->getHelper()->getCheckoutSession()->getMessages(true);

        try {
            $result = $this->getRequest()->getParam(self::URL_PARAM_RESULT);
            $this->getLogger()->debug($this->getHelper()->__('Checkout Result: %s', $result));

            $checkoutId = $this->getHelper()->getCheckoutIdFromSession();

            // if checkout id can't be found in the checkout session
            if(empty($checkoutId)) {

                // get checkout id from checkout url parameter
                // filter the result to remove additional GTM string
                $checkoutId = preg_replace('/\?.+$/', '', $this->getRequest()->getParam(self::URL_PARAM_CHECKOUT_ID) ?: '');

                if (empty($checkoutId)) {
                    Mage::throwException($this->getHelper()->__('The checkoutId does not exist'));
                }

                $this->processResponseResult($result, $checkoutId);
            }
            else {

                $this->processResponseResult($result);
                $this->getHelper()->unsetCheckoutSessionData();

            }

        } catch (Exception $e) {
            $errorMessage = $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_GENERAL_ERROR_PATH));
            $this->getHelper()->getCheckoutSession()->addError($errorMessage);
            $this->getLogger()->error($e->getMessage());
            $this->getHelper()->getCheckoutSession()->addError($e->getMessage());
        }

    }

    /**
     * handle redirect url from checkout js
     */
    public function startAction() {

        if(!$this->getHelper()->isActive()) {
            return;
        }

        $checkoutId = Mage::helper('zip_payment')->getCheckoutIdFromSession();
        $redirectUrl = Mage::helper('zip_payment')->getCheckoutRedirectUrlFromSession();

        /**
         * re-generate checkout session data if there is any one empty
         */
        if(empty($checkoutId) || empty($redirectUrl)) {
            Mage::helper('zip_payment')->getCurrentPaymentMethod()->getCheckoutRedirectUrl();
            $checkoutId = Mage::helper('zip_payment')->getCheckoutIdFromSession();
            $redirectUrl = Mage::helper('zip_payment')->getCheckoutRedirectUrlFromSession();
        }

        $response = array(
            'id' => $checkoutId,
            'uri' => $redirectUrl,
            'redirect_uri' => $redirectUrl
        );

        $this->returnJsonResponse($response);
    }

    /**
     * Action to handle checkout errors
     */
    public function failureAction() {
        
        if(!$this->getHelper()->isActive()) {
            return;
        }

        $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - failure action'));

        try {

            $this->loadLayout();
            $this->createBreadCrumbs('zip_payment_checkout_failure', 'Checkout Failure');
            $this->renderLayout();
            $this->getLogger()->debug($this->getHelper()->__('Successfully redirect to the failure page.'));

        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            Mage::getSingleton('checkout/session')->addError($this->getHelper()->__('An error occurred during redirecting to failure page.'));
        }

    }

    /**
     * Action to handle checkout errors
     */
    public function referredAction() {

        if(!$this->getHelper()->isActive()) {
            return;
        }
        
        $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - referred action'));

        try {

            $this->loadLayout();
            $this->createBreadCrumbs('zip_payment_checkout_referred', 'Checkout Referred');
            $this->renderLayout();
            $this->getLogger()->debug($this->getHelper()->__('Successfully redirect to the referred page.'));

        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            Mage::getSingleton('checkout/session')->addError($this->getHelper()->__('An error occurred during redirecting to referred page.'));
        }

    }



}