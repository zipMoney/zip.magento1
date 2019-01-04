<?php

class Zip_Payment_CheckoutController extends Zip_Payment_Controller_Checkout
{

    /**
     * Handling response from API response
     *
     * @throws Mage_Core_Exception
     */
    public function responseAction()
    {

        $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - responseAction'));
        $errorMessage = '';

        try {
            
            $result = $this->getRequest()->getParam(self::URL_PARAM_RESULT);
            $checkoutId = $this->getHelper()->getCheckoutSessionId();

            $this->getLogger()->debug($this->getHelper()->__('Checkout Result: %s',  $result));

            if (empty($checkoutId)) {
                Mage::throwException($this->getHelper()->__('The checkoutId does not exist'));
            }

        } catch (Exception $e) {
            $errorMessage = $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_GENERAL_ERROR_PATH));
            $this->getHelper()->getCheckoutSession()->addError($errorMessage);
            $this->getLogger()->error($e->getMessage());
            $this->getHelper()->unsetCheckoutSessionId();
            $this->redirectToCartOrError();
            return;
        }

        $response = Mage::getSingleton('zip_payment/checkout')->processResponseResult($result);
        $this->getHelper()->unsetCheckoutSessionId();

        if($this->getRequest()->isAjax()) {
            $this->returnJsonResponse($response);
        }
        else {

            if($response['success']) {
                $this->redirectToSuccess();
            }
            else {
                $this->redirectToCartOrError();
            }
            
        }
    }

    /**
     * handle redirect url from checkout js
     */
    public function redirectAction() {

        if(!$this->getHelper()->isActive()) {
            return;
        }

        $redirectUrl = urldecode($this->getRequest()->getParam('redirect_url'));

        $response = array(
            'id' => Mage::helper('zip_payment')->getCheckoutSessionId(),
            'uri' => $redirectUrl,
            'redirect_uri' => $redirectUrl
        );

        $this->returnJsonResponse($response);
    }

    /**
     * Action to handle checkout errors
     */
    public function failureAction() {
        
        $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - failureAction'));

        try {

            $this->loadLayout();

            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

            if($breadcrumbs) {

                $breadcrumbs->addCrumb('home', array(
                    'label' => $this->__('Home'),
                    'title' => $this->__('Home'),
                    'link'  => Mage::getBaseUrl()
                ));

                $isLandingPageEnabled = Mage::getSingleton('zip_payment/config')->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);

                if($isLandingPageEnabled) {
                    $breadcrumbs->addCrumb('zip_payment', array(
                        'label' => $this->__('Zip Payment'),
                        'title' => $this->__('Zip Payment'),
                        'link'  => $this->getHelper()->getUrl(Zip_Payment_Model_Config::LANDING_PAGE_URL_ROUTE)
                    ));
                }

                $breadcrumbs->addCrumb('zip_payment_checkout_error', array(
                    'label' => $this->__('Checkout Error'),
                    'title' => $this->__('Checkout Error')
                ));

            }

            $this->renderLayout();
            $this->getLogger()->debug($this->getHelper()->__('Successfully redirect to the failure page.'));

        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            Mage::getSingleton('checkout/session')->addError($this->getHelper()->__('An error occurred during redirecting to failure page.'));
        }

    }



}