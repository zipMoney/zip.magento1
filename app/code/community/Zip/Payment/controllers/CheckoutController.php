<?php

/**
 * Checkout controller
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Model\CurrencyUtil;

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

        $iframe = $this->getRequest()->getParam(Zip_Payment_Model_Config::URL_PARAM_IFRAME);

        try {
            // as AU stack already handle iframe in redirect
            $currencyCode = $this->getHelper()->getCheckoutSession()->getQuote()->getQuoteCurrencyCode();
            if ($iframe && $currencyCode !== CurrencyUtil::CURRENCY_AUD) {
                $this->loadLayout();
                $url = $this->getResponseUrl($checkoutId, $state);
                $block = $this->getLayout()
                    ->createBlock('core/template')
                    ->setTemplate('zip/payment/iframe/iframe_js.phtml');
                /*
                 *
                 * $block = $this->getLayout()->createBlock(
'Mage_Core_Block_Template',
'my_block_name_here',
array('template' => 'activecodeline/developer.phtml')
);

$this->getLayout()->getBlock('content')->append($block);

//Release layout stream... lol... sounds fancy
$this->renderLayout();
                 */
                $block->setData('checkoutId', $checkoutId);
                $block->setData('state', $state);
                $block->setData('redirectUrl', $url);
                $this->getLayout()->getBlock('content')->append($block);
                $this->renderLayout();
            }
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

    /**
     * Returns the response url.
     *
     * @return string
     */
    public function getResponseUrl($checkoutId, $state)
    {
        return $this->getHelper()
                ->getUrl(Zip_Payment_Model_Config::CHECKOUT_RESPONSE_URL_ROUTE) .
            '?' . Zip_Payment_Model_Config::URL_PARAM_CHECKOUT_ID . '='.$checkoutId.'&' .
            Zip_Payment_Model_Config::URL_PARAM_RESULT . '='.$state;
    }



}
