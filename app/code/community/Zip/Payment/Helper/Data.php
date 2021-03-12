<?php

/**
 * Helper functions
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * get config model
     */
    public function getConfig()
    {
        return Mage::getSingleton('zip_payment/config');
    }

    /**
     * is Zip Payment active
     */
    public function isActive()
    {
        return $this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_ACTIVE_PATH);
    }

    /**
     * Retrieves the extension version.
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return trim((string) Mage::getConfig()->getNode()->modules->Zip_Payment->version);
    }

    /**
     * Get current store url
     *
     * @param  $route
     * @param  $param
     * @return string
     */
    public function getUrl($route, $param = array())
    {
        return Mage::getUrl($route, $param);
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * get payment method currently been used
     */
    public function getCurrentPaymentMethod()
    {
        return $this->getOnepage()->getQuote()->getPayment()->getMethodInstance();
    }


    /**
     * Empty customer's shopping cart
     */
    public function emptyShoppingCart()
    {
        try {
            $this->getCheckoutSession()->getQuote()->setIsActive(0)->save();
            $this->getCheckoutSession()->setQuoteId(null);
        } catch (Mage_Core_Exception $exception) {
            $this->getCheckoutSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->getCheckoutSession()->addException($exception, $this->__('Could not empty shopping cart'));
        }
    }

    /**
     * Prepare JSON formatted data for response to client
     *
     * @param  $response
     * @return Zend_Controller_Response_Abstract
     */
    public function returnJsonResponse($response)
    {
        Mage::app()->getFrontController()->getResponse()->setHeader('Content-type', 'application/json', true);
        Mage::app()->getFrontController()->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * check whether checkout display model is redirect
     */
    public function isRedirectCheckoutDisplayModel()
    {
        return true; //iframe checking is disable until we fix zip checkout js issue to support iframe for all browse
    }

    /*******************************************
     * PAGE DETECTION
     *******************************************/

    /**
     * get full action name for current page
     */
    public function getPageIdentifier()
    {
        return Mage::app()->getFrontController()->getAction()->getFullActionName();
    }

    /**
     * get path for current page
     */
    public function getPagePath()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
        return rtrim($url->getPath(), '/');
    }

    /**
     * is currently checkout page
     */
    public function isCheckoutPage()
    {
        $path = $this->getPagePath();
        $checkoutPath = rtrim($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_PATH_PATH), '/');
        return strpos($path, $checkoutPath) !== false;
    }

    /**
     * is currently one page checkout
     */
    public function isOnePageCheckout()
    {
        return $this->isCheckoutPage() &&
            $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_TYPE_PATH) ==
            Zip_Payment_Model_Adminhtml_System_Config_Source_CheckoutType::CHECKOUT_TYPE_ONE_PAGE;
    }

    /**
     * is current page onestep checkout
     */
    public function isOneStepCheckout()
    {
        return $this->isCheckoutPage() &&
        $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_TYPE_PATH) ==
        Zip_Payment_Model_Adminhtml_System_Config_Source_CheckoutType::CHECKOUT_TYPE_ONE_STEP;
    }

    /**
     * check whether an order is a referred order
     */
    public function isReferredOrder($order)
    {
        if ($order && $order->getId()) {
            return $order->getStatus() === $this
                ->getConfig()
                ->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_REFERRED_ORDER_STATUS_PATH);
        }

        return false;
    }

    /**
     * check whether an order is a pickup order
     */
    public function isPickupOrder($order)
    {
        if ($order && $order->getId()) {
            // virtual product will use pick up shipping method
            if($order->getIsVirtual()) {
                return true;
            }
            $shippingAddress = $order->getShippingAddress();
            if($shippingAddress == null) {
                return true;
            }
            $shippingMethod = $shippingAddress->getShippingMethod();
            if(empty($shippingMethod)) {
                return true;
            }
            // Check click and collect and set pickup as true
            $clickCollect = $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_CLICK_COLLECT_PATH);
            if(!empty($clickCollect) && preg_match('/.' . $clickCollect .  '*/', $shippingMethod)) {
                return true;
            }
        }

        return false;
    }


    /*******************************************
     * SESSION
     *******************************************/

    /**
     * save checkout session data
     */
    public function saveCheckoutSessionData($data)
    {
        $sessionData = $this->getCheckoutSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);

        if ($sessionData) {
            $data = array_merge($sessionData, $data);
        }

        $this->getCheckoutSession()->setData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY, $data);
    }

    /**
     * get checkout session data
     */
    public function getCheckoutSessionData()
    {
        return $this->getCheckoutSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);
    }

    /**
     * get checkout id from checkout session
     *
     * @return string
     */
    public function getCheckoutIdFromSession()
    {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY]) ?
            $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY] : null;
    }

    /**
     * get checkout redirect url from checkout session
     *
     * @return string
     */
    public function getCheckoutRedirectUrlFromSession()
    {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_REDIRECT_URL_KEY]) ?
            $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_REDIRECT_URL_KEY] : null;
    }

    /**
     * get checkout result from checkout session
     *
     * @return string
     */
    public function getCheckoutStateFromSession()
    {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_STATE_KEY]) ?
            $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_STATE_KEY] : null;
    }

    /**
     * unset checkout session data
     */
    public function unsetCheckoutSessionData()
    {
        $this->getCheckoutSession()->unsetData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);
    }

    /**
     * check if current checkout state is referred
     */
    public function isReferredCheckout()
    {
        return $this->getCheckoutStateFromSession() == Zip_Payment_Model_Api_Checkout::STATE_REFERRED;
    }


    /**
     * delete log file
     */
    public function removeLogFile($filename)
    {
        $path = Mage::getBaseDir('var') . DS . 'log' . DS . $filename;
        $io = new Varien_Io_File();
        $io->rm($path);
    }
}
