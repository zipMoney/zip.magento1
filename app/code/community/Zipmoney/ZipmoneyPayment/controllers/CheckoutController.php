<?php

class Zipmoney_ZipmoneyPayment_CheckoutController extends Mage_Core_Controller_Front_Action
{
    protected $logger;

    protected function _construct()
    {
        $this->logger = Mage::getSingleton('zipmoneypayment/logger');
    }

    protected function responseJSON($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    protected function ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    protected function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->ajaxRedirectResponse();
            return true;
        }

        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)) {
            $this->ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    protected function validateRequest()
    {
        //check if cart empty or changed
        if ($this->expireAjax()) {
            return false;
        }

        if (!$this->getRequest()->isPost()) {
            $this->ajaxRedirectResponse();
            return false;
        }

        return true;
    }

    public function indexAction()
    {
        if (!$this->validateRequest()) {
            return;
        }

        $this->logger->debug("Checkout Controller Start.");
        try {
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
                $result['redirect_uri'] = $redirectUrl;
                $this->logger->debug('Redirect to zip checkout url: ' . $redirectUrl);
            }

            $result['message'] = $this->__('Redirecting to Zip Payment Page.');
            return $this->responseJSON($result);
        } catch (Mage_Payment_Exception $e) {
            $this->logger->error($e->getMessage());
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }

            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $this->logger->error($e->getMessage());
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

        return $this->responseJSON($result);
    }
}