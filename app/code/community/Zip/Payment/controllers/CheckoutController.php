<?php

use Zip\ApiException;

class Zip_Payment_CheckoutController extends Zip_Payment_Controller_Checkout
{   

    /**
     * Start the checkout by requesting the redirect url and checkout id
     *
     * @return json
     * @throws Mage_Core_Exception
     */
    public function responseAction()
    {
        $this->getLogger()->info($this->getHelper()->__('On Complete Action'));

        try {
            
            $result = $this->getRequest()->getParam(self::URL_PARAM_RESULT);
            $checkoutId = $this->getHelper()->getCheckoutSessionId();

            $this->getLogger()->debug($this->getHelper()->__('Checkout Result: %s',  $result));

            if (empty($checkoutId)) {
                Mage::throwException($this->getHelper()->__('The checkoutId does not exist'));
            }

        } catch (Exception $e) {
            $this->getHelper()->getCheckoutSession()->addError($this->getHelper()->__('Unable to complete the checkout.'));
            $this->getLogger()->error($e->getMessage());
            $this->redirectToCartOrError();
            return;
        }

        /* Handle the checkout result */
        switch ($result) {
            case 'approved':
                try {
                    $onepage = $this->getHelper()->getOnepage();
                    $onepage->getQuote()->collectTotals();
                    $onepage->saveOrder();
                    
                    $this->getHelper()->unsetCheckoutSessionId();
                    $this->getLogger()->log('Order is been saved and redirect to success page');
                    $this->redirectToSuccess();
                    return;
                } catch (Exception $e) {
                    $this->getHelper()->getCheckoutSession()->addError($this->getHelper()->__('An error occurred during the checkout.'));
                    $this->getLogger()->debug($e->getMessage());
                }

                $this->redirectToCartOrError();
                break;
            case 'declined':
            case 'cancelled':
            case 'referred':
                $this->getHelper()->unsetCheckoutSessionId();
                $this->getLogger()->info($this->getHelper()->__('Checkout has been ' . $result));
                $this->redirectToCartOrError();
                break;
            default:
                // Dispatch the referred action
                $this->redirectToCartOrError();
                break;
        }
    }
}
