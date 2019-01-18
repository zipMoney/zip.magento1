<?php

/**
 * Helper functions
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * get config model
     */
    public function getConfig() {
        return Mage::getSingleton('zip_payment/config');
    }

    /**
     * is Zip Payment active
     */
    public function isActive() {
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
     * autoload API SDK from lib folder
     */
    public function autoload() {
        require_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';
    }

    /**
     * Get current store url
     *
     * @param $route
     * @param $param
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
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    public function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * get payment method currently been used
     */
    public function getCurrentPaymentMethod() {
        return $this->getOnepage()->getQuote()->getPayment()->getMethodInstance();
    }


    /**
     * Empty customer's shopping cart
     */
    public function emptyShoppingCart()
    {
        try {
            $this->getCart()->truncate()->save();
            $this->getCheckoutSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->getCheckoutSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->getCheckoutSession()->addException($exception, $this->__('Cannot empty shopping cart'));
        }
    }

    /******************************************* PAGE DETECTION ************************************************* */

    /**
     * get full action name for current page
     */
    public function getPageIdentifier() {
        return Mage::app()->getFrontController()->getAction()->getFullActionName();
    }

    /**
     * is currently using one page checkout
     */
    public function isOnepageCheckout() {
        return $this->getPageIdentifier() == Zip_Payment_Model_Config::ONEPAGE_CHECKOUT_IDENTIFIER;
    }

    /**
     * checkout current page is onestep checkout
     */
    public function isOnestepCheckout() {
        return $this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_CHECKOUT_ONESTEPCHECKOUTS_PATH . '/' . $this->getPageIdentifier());
    }

    /**
     * check whether an order is a referred order
     */
    public function isReferredOrder($order) {

        if($order && $order->getId()) {
            return $order->getStatus() === $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_REFERRED_ORDER_STATUS_PATH);
        }

        return false;
        
    }


    /******************************************* SESSION ************************************************* */

    /**
     * save checkout session data
     */
    public function saveCheckoutSessionData($data) {
        $sessionData = $this->getCheckoutSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);

        if($sessionData) {
            $data = array_merge($sessionData, $data);
        }
        $this->getCheckoutSession()->setData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY,  $data);
    }

    /**
     * get checkout session data
     */
    public function getCheckoutSessionData() {
       return $this->getCheckoutSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);
    }

     /**
     * get checkout id from checkout session
     * 
     * @return string
     */
    public function getCheckoutIdFromSession() {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY]) ? $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY] : null;
    }

    /**
     * get checkout redirect url from checkout session
     * 
     * @return string
     */
    public function getCheckoutRedirectUrlFromSession() {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_REDIRECT_URL_KEY]) ? $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_REDIRECT_URL_KEY] : null;
    }

    /**
     * get checkout result from checkout session
     * 
     * @return string
     */
    public function getCheckoutStateFromSession() {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_STATE_KEY]) ? $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_STATE_KEY] : null;
    }

    /**
     * unset checkout session data
     */
    public function unsetCheckoutSessionData() {
        $this->getCheckoutSession()->unsetData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);
    }


}
