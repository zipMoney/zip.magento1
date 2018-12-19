<?php

use Zip\ApiException;


class Zip_Payment_CheckoutController extends Zip_Payment_Controller_Checkout
{   

    /**
     * 
     */
    public function indexAction() {

        // handle ajax call
        if ($this->getRequest()->isAjax()) {
            
            $response = $this->getRequest()->getParam('response');

            if(!empty($response)){
                $response = json_decode(urldecode($response));
            }

            echo Mage::helper('core')->jsonEncode($response);
            exit;

        }
        
    }

    /**
     * Handling response from API response
     *
     * @throws Mage_Core_Exception
     */
    public function responseAction()
    {
        $this->getLogger()->debug($this->getHelper()->__('Checkout Controller - responseAction'));
        $errorMessage = '';

        try {
            
            $result = $this->getRequest()->getParam(self::URL_PARAM_RESULT);
            $checkoutId = $this->getHelper()->getCheckoutSessionId();

            $this->getLogger()->debug($this->getHelper()->__('Checkout Result: %s',  $result));

            if (empty($checkoutId)) {
                Mage::throwException($this->getHelper()->__('The checkoutId does not exist'));
            }
            else {
                $this->getHelper()->unsetCheckoutSessionId();
            }

        } catch (Exception $e) {
            $errorMessage = $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_GENERAL_ERROR_PATH));
            $this->getHelper()->getCheckoutSession()->addError($errorMessage);
            $this->getLogger()->error($e->getMessage());
            $this->redirectToCartOrError();
        }

        /* Handle the checkout result */
        switch ($result) {
            case Zip_Payment_Model_Api_CheckoutResponseResult::APPROVED:
                try {
                    $onepage = $this->getHelper()->getOnepage();
                    $onepage->getQuote()->collectTotals();
                    $onepage->saveOrder();
                    
                    $this->getLogger()->log('Order is been saved and redirecting to success page');
                    $this->redirectToSuccess();

                } catch (Exception $e) {
                    $this->getHelper()->getCheckoutSession()->addError($this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_GENERAL_ERROR_PATH)));
                    $this->getLogger()->error($e->getMessage());
                    $this->redirectToCartOrError();
                }

                break;
            case Zip_Payment_Model_Api_CheckoutResponseResult::DECLINED:
            case Zip_Payment_Model_Api_CheckoutResponseResult::CANCELLED:
            case Zip_Payment_Model_Api_CheckoutResponseResult::REFERRED:

                $errorMessage = $this->getHelper()->__('Checkout has been ' . $result);
                $this->getHelper()->getCheckoutSession()->addError($errorMessage);

                $additionalErrorMessage = $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_ERROR_PATH_PREFIX . $result));
                if($additionalErrorMessage) {
                    $this->getHelper()->getCheckoutSession()->addError($additionalErrorMessage);
                }

                $this->getLogger()->debug($errorMessage);
                break;
                
            default:
                $errorMessage = $this->getHelper()->__('Something wrong while processing checkout');
                $this->getHelper()->getCheckoutSession()->addError($errorMessage);
                $this->getLogger()->debug($errorMessage);
                break;
        }


        if($this->getRequest()->isAjax()) {
            $this->returnJsonResponse(array(
                'error_message' => $result == Zip_Payment_Model_Api_CheckoutResponseResult::CANCELLED ? null : $errorMessage
            ));
        }
        else {
            $this->redirectToCartOrError();
        }
    }

    /**
     * Action to handle checkout errors
     */
    public function errorAction() {
        
        $this->getLogger()->debug($this->getHelper()->__('Checkout Controller - errorAction'));

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
            $this->getLogger()->debug($this->getHelper()->__('Successfully redirect to error page.'));

        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            Mage::getSingleton('checkout/session')->addError($this->getHelper()->__('An error occurred during redirecting to error page.'));
        }

    }

}
