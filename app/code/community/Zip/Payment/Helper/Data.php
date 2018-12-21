<?php

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';

    public function isActive() {
        return Mage::getSingleton('zip_payment/config')->getFlag(self::CONFIG_ACTIVE_PATH);
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


    public function getCheckoutSessionId() {
        return $this->getCheckoutSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_ID);
    }

    public function setCheckoutSessionId($id) {
        return $this->getCheckoutSession()->setData(Zip_Payment_Model_Config::CHECKOUT_SESSION_ID, $id);
    }

    public function unsetCheckoutSessionId() {
        $this->getCheckoutSession()->unsetData(Zip_Payment_Model_Config::CHECKOUT_SESSION_ID);
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

    public function getCurrentPaymentMethod() {
        return $this->getOnepage()->getQuote()->getPayment()->getMethodInstance()->getCode();
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

    
}
