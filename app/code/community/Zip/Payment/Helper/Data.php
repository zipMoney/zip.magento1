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
    public function autoLoad() {
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
            $this->getCheckoutSession()->addException($exception, Mage::helper('zip_payment')->__('Cannot empty shopping cart'));
        }
    }



    /******************************************* SESSION ************************************************* */

    /**
     * save checkout session data
     */
    public function saveCheckoutSessionData($data) {
        $this->getCheckoutSession()->setData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY, $data);
    }

    /**
     * get checkout session id
     * 
     * @return string
     */
    public function getCheckoutSessionId() {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY]) ? $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY] : null;
    }

    /**
     * get checkout session redirect url
     * 
     * @return string
     */
    public function getCheckoutSessionRedirectUrl() {
        $sessionData = $this->getCheckoutSessionData();
        return isset($sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_REDIRECT_URL_KEY]) ? $sessionData[Zip_Payment_Model_Api_Checkout::CHECKOUT_REDIRECT_URL_KEY] : null;
    }

    /**
     * get checkout session data
     */
    public function getCheckoutSessionData() {
       return  $this->getCheckoutSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);
    }

    /**
     * unset checkout session data
     */
    public function unsetCheckoutSessionData() {
        $this->getCheckoutSession()->unsetData(Zip_Payment_Model_Config::CHECKOUT_SESSION_KEY);
    }


}
