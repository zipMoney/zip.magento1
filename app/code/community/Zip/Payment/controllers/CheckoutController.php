<?php

use Zip\ApiException;

class Zip_Payment_CheckoutController extends Zip_Payment_Controller_Checkout
{   
    
    
    public function startAction()
    {
        $checkoutId = $this->getSession()->getZipCheckoutId();

        if (empty($checkoutId)) {

            $quote = $this->getQuote();

            if (!$quote->hasItems() || $quote->getHasError()) {
                Mage::throwException($this->getHelper()->__('Unable to initialize the Checkout.'));
            }

            try {

                $redirectUrl = Mage::getModel('zip_payment/api_checkout')
                ->setApiConfig($this->getApiConfig())
                ->setQuote($quote)
                ->create()
                ->getRedirectUrl();

                $this->getResponse()->setRedirect($redirectUrl);
                return;

            } catch (Exception $e) {
                Mage::throwException($this->_getHelper()->__('Failed to process checkout - ' . $e->getMessage()));
            }
            
        }

        $this->_redirect('checkout/cart');
    }

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
            if (!$this->isResultValid()) {
                $this->redirectToCartOrError();
                return;
            }

            $result = $this->getRequest()->getParam(self::URL_PARAM_RESULT);
            $checkoutId = $this->getRequest()->getParam(self::URL_PARAM_CHECKOUT_ID);

            $this->getLogger()->debug($this->getHelper()->__('Checkout Result: %s',  $result));

            if (empty($checkoutId)) {
                Mage::throwException($this->getHelper()->__('The checkoutId does not exist'));
            }

        } catch (Exception $e) {
            $this->getCheckoutSession()->addError($this->getHelper()->__('Unable to complete the checkout.'));
            $this->getLogger()->error($e->getMessage());
            $this->redirectToCartOrError();
            return;
        }

        /* Handle the checkout result */
        switch ($result) {
            case 'approved':
                 /**
                 * - Create order
                 * - Charge the customer using the checkout id
                 */
                
                try {
                    $this->getQuote()->setIsActive(false)->save();
                    var_dump('test');
                    $this->_redirect('checkout/onepage/success', array('_secure' => true));
                    return;
                } catch (Exception $e) {
                    $this->getCheckoutSession()->addError($this->getHelper()->__('An error occurred during the checkout.'));
                    $this->getLogger()->debug($e->getMessage());
                }

                $this->redirectToCartOrError();
                break;
            case 'declined':
                $this->getLogger()->info($this->getHelper()->__('Checkout has been declined'));
                $this->redirectToCartOrError();
                break;
            case 'cancelled':
                $this->getLogger()->debug($this->getHelper()->__('Checkout has been cancelled'));
                $this->redirectToCartOrError();
                break;
            case 'referred':
                $this->getHelper()->deactivateQuote($this->_getQuote());
                // Dispatch the referred action
                //$this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/referred');
                $this->redirectToCartOrError();
                break;
            default:
                // Dispatch the referred action
                $this->redirectToCartOrError();
                break;
        }
    }

    /****************************************************************** */


    /**
     * Charges Api Class
     *
     * @var string
     */
    protected $_apiClass  = '\zipMoney\Client\Api\ChargesApi';

    /**
     * Charge Model
     *
     * @var string
     */
    protected $_chargeModel = 'zipmoneypayment/charge';

    /**
     * Return from zipMoney and handle the result of the application
     *
     * @throws Mage_Core_Exception
     */
    public function redirectAction()
    {
        $this->getLogger()->debug($this->getHelper()->__("On Complete Controller"));

        try {
            // Is result valid ?
            if (!$this->isResultValid()) {
                $this->redirectToCartOrError();
                return;
            }

            $result = $this->getRequest()->getParam('result');
            $this->getLogger()->debug($this->getHelper()->__("Result:- %s", $result));
            // Is checkout id valid?
            if (!$this->getRequest()->getParam('checkoutId')) {
                Mage::throwException($this->getHelper()->__("The checkoutId doesnot exist in the querystring."));
            }



            // Set the customer quote
            // $this->_setCustomerQuote();
            // Initialise the charge
            $this->_initCharge();
            // Set quote to the chekout model
            $this->_charge->setQuote($this->_getQuote());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->getHelper()->__('Unable to complete the checkout.'));
            $this->getLogger()->error($e->getMessage());
            $this->redirectToCartOrError();
            return;
        }

        $order_status_history_comment = '';

        /* Handle the application result */
        switch ($result) {
            case 'approved':
                /**
                 * - Create order
                 * - Charge the customer using the checkout id
                 */
                try {
                    // Create the Order
                    $order = $this->_charge->placeOrder();
                    $this->_charge->charge();
                    // Redirect to success page
                    $this->_redirect('checkout/onepage/success');
                    return;
                } catch (Mage_Core_Exception $e) {
                    $this->_getCheckoutSession()->addError($e->getMessage());
                    $this->getLogger()->debug($e->getMessage());
                } catch (Exception $e) {
                    $this->_getCheckoutSession()->addError($this->getHelper()->__('An error occurred during the checkout.'));
                    $this->getLogger()->debug($e->getMessage());
                }

                $this->redirectToCartOrError();
                break;
            case 'declined':
                $this->getLogger()->debug($this->getHelper()->__('Calling declinedAction'));
                $this->redirectToCartOrError();
                break;
            case 'cancelled':
                $this->getLogger()->debug($this->getHelper()->__('Calling cancelledAction'));
                $this->redirectToCartOrError();
                break;
            case 'referred':
                // Make sure the qoute is active
                $this->getHelper()->deactivateQuote($this->_getQuote());
                // Dispatch the referred action
                $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE . '/referred');
                break;
            default:
                // Dispatch the referred action
                $this->redirectToCartOrError();
                break;
        }
    }

    /**
     * Start the checkout by requesting the redirect url and checkout id
     *
     * @return json
     * @throws Mage_Core_Exception
     */
    public function indexAction()
    {
        var_dump('test'); die;
        if ($this->_expireAjax()) {
            return;
        }

        $exception_message = null;

        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            if ($data = $this->getRequest()->getPost('payment', array())) {
                $result = $this->getOnepage()->savePayment($data);

                if (empty($result['error'])) {
                    $this->getLogger()->info($this->getHelper()->__('Payment method saved'));
                    $review = $this->getRequest()->getPost('review');
                    if (isset($review) && $review == "true") {
                        $this->loadLayout('checkout_onepage_review');
                        $result['goto_section'] = 'review';
                        $result['update_section'] = array(
                          'name' => 'review',
                          'html' => $this->getLayout()->getBlock('root')->toHtml()
                          );
                    }
                } else {
                    Mage::throwException($this->getHelper()->__("Failed to save the payment method"));
                }
            }

            $this->getLogger()->info($this->getHelper()->__('Starting Checkout'));
            /*
            -Initialize the checkout model
            -Start the checkout process
            */
            $this->_initCheckout()->start();
            if ($redirectUrl = $this->_checkout->getRedirectUrl()) {
                $this->getLogger()->info($this->getHelper()->__('Successful to get redirect url [ %s ] ', $redirectUrl));
                $result['redirect_uri'] = $redirectUrl;
                $result['message']  = $this->getHelper()->__('Redirecting to zipMoney.');
                return $this->_sendResponse($result, Mage_Api2_Model_Server::HTTP_OK);
            } else {
                Mage::throwException("Failed to get redirect url.");
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }

            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $exception_message = $e->getMessage();

            if ($e->getCode() != 1000) {
                $this->getLogger()->debug($e->getMessage());
            }
        } catch(\InvalidArgumentException $e){
            $this->getLogger()->debug("InvalidArgumentException:-".$e->getMessage());
            $result['error'] = "Invalid arguments provided.\n\nError Detail:- ".$e->getMessage();
        } catch (Exception $e) {
            $this->getLogger()->debug($e->getMessage());
        }

        if (empty($result['error'])) {
            $result['error'] = $this->getHelper()->__('An error occurred while trying to checkout with zip.');
        }

        if (!is_null($exception_message)) {
            $result['exception_message'] = $exception_message;
        }

        $this->_sendResponse($result, Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
    }

    /**
     * Return from zipMoney and handle the result of the application
     */
    public function chargeAction()
    {
        $session = $this->_getCheckoutSession();

        $orderId = $session->getLastOrderId();
        $quoteId = $session->getLastQuoteId();

        $order = Mage::getSingleton("sales/order")->load($orderId);
        $quote = Mage::getSingleton("sales/quote")->load($quoteId);

        $this->getLogger()->debug($this->getHelper()->__("On Charge Order Action"));

        try {
            // Check if the quote exists
            if (!$quote->getId()) {
                Mage::throwException($this->getHelper()->__("The quote doesnot exist."));
            }

            if (!$order->getId()) {
                Mage::throwException($this->getHelper()->__("The order doesnot exist."));
            }

            // Check if the zipMoney Checkout Id Exists
            if (!$quote->getZipmoneyCid()) {
                Mage::throwException($this->getHelper()->__("The order has not been approved by zipMoney or the zipMoney Checkout Id doesnot exist."));
            }

            // Check if the Order Has been charged
            if ($order->getPayment()->getZipmoneyChargeId()) {
                Mage::throwException($this->getHelper()->__("The order has already been charged."));
            }

            // Initialise the charge
            $this->_charge = Mage::getSingleton('zipmoneypayment/charge');
            // Set quote to the chekout model
            $this->_charge->setOrder($order)->charge();
            return $this->getResponse()->setRedirect($this->getHelper()->getUrl('checkout/onepage/success'));
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->getLogger()->debug($e->getMessage());
        } catch (Exception $e) {
            $this->getLogger()->debug($e->getMessage());
            $this->_getCheckoutSession()->addError($this->getHelper()->__('An error occurred while to trying to complete the checkout.'));
        }

        $this->redirectToCartOrError();
    }
}
