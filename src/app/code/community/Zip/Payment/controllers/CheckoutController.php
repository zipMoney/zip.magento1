<?php

/**
 * Checkout controller
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
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
        if (!$this->getHelper()->isActive()) {
            return;
        }

        $this->getLogger()->debug('Zip_Payment_CheckoutController - responseAction');
        $this->getHelper()->getCheckoutSession()->getMessages(true);

        // get response result
        $state = $this->getRequest()->getParam(Zip_Payment_Model_Config::URL_PARAM_RESULT);
        // get checkout id from checkout url parameter
        // filter the result to remove additional GTM string
        $checkoutId = preg_replace(
            '/\?.+$/', '',
            $this->getRequest()->getParam(Zip_Payment_Model_Config::URL_PARAM_CHECKOUT_ID) ?: ''
        );

        try {
            $response = Mage::getSingleton('zip_payment/checkout')->handleResponse($checkoutId, $state);
            $this->redirectAfterResponse($response);
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getHelper()->getCheckoutSession()->addError($e->getMessage());
        }

    }

    /**
     * handle redirect url from checkout js
     */
    public function startAction()
    {
        if (!$this->getHelper()->isActive()) {
            return;
        }

        $checkoutId = $this->getHelper()->getCheckoutIdFromSession();
        $redirectUrl = $this->getHelper()->getCheckoutRedirectUrlFromSession();

        /**
         * re-generate checkout session data if there is any one empty
         */
        if (empty($checkoutId) || empty($redirectUrl)) {
            $this->getHelper()->getCurrentPaymentMethod()->getCheckoutRedirectUrl();
            $checkoutId = $this->getHelper()->getCheckoutIdFromSession();
            $redirectUrl = $this->getHelper()->getCheckoutRedirectUrlFromSession();
        }

        $response = array(
            'id' => $checkoutId,
            'uri' => $redirectUrl,
            'redirect_uri' => $redirectUrl
        );

        $this->getHelper()->returnJsonResponse($response);
    }

    /**
     * Action to handle checkout errors
     */
    public function failureAction()
    {
        if (!$this->getHelper()->isActive()) {
            return;
        }

        $this->getLogger()->debug('Zip_Payment_CheckoutController - failure action');

        try {
            $this->loadLayout();
            $this->createBreadCrumbs('zip_payment_checkout_failure', 'Checkout Failure');
            $this->renderLayout();
            $this->getLogger()->debug('Successfully redirect to the failure page.');
        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            Mage::getSingleton('checkout/session')
                ->addError(
                    $this->getHelper()->__('An error occurred during redirecting to failure page.')
                );
        }

    }

    /**
     * Action to handle checkout errors
     */
    public function referredAction()
    {
        if (!$this->getHelper()->isActive()) {
            return;
        }

        $this->getLogger()->debug('Zip_Payment_CheckoutController - referred action');

        try {
            $this->loadLayout();
            $this->createBreadCrumbs('zip_payment_checkout_referred', 'Checkout Referred');
            $this->renderLayout();
            $this->getLogger()->debug('Successfully redirect to the referred page.');
        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            Mage::getSingleton('checkout/session')
                ->addError(
                    $this->getHelper()->__('An error occurred during redirecting to referred page.')
                );
        }

    }



}
