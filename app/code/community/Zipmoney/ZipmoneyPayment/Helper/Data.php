<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Helper_Data extends Zipmoney_ZipmoneyPayment_Helper_Abstract
{

    /**
     * Returns the checkout scripts
     *
     * @return string
     */
    public function getCheckoutJsLibUrl()
    {
        return '<script src="https://static.zipmoney.com.au/checkout/checkout-v1.min.js"></script><script src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true).'zipmoney/dist/scripts/zipcheckout.js?v1.0"></script>';
    }

    /**
     * Returns the json_encoded string
     *
     * @return string
     */
    public function json_encode($object)
    {
        return json_encode(\zipMoney\ObjectSerializer::sanitizeForSerialization($object));
    }

    /**
     * Get current store url
     *
     * @param $route
     * @param $param
     * @return string
     */
    public function getUrl($route, $param = array('_secure' => true))
    {
        $storeId = Mage::getSingleton('zipmoneypayment/storeScope')->getStoreId();
        if ($storeId !== null) {
            $store = Mage::app()->getStore($storeId);
            $url = $store->getUrl($route, $param);
        } else {
            $url = Mage::getUrl($route, $param);
        }

        return $url;
    }

    /**
     * @param $oQuote
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function activateQuote($quote)
    {
        if ($quote && $quote->getId()) {
            if (!$quote->getIsActive()) {
                $orderIncId = $quote->getReservedOrderId();
                if ($orderIncId) {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncId);
                    if ($order && $order->getId()) {
                        Mage::throwException($this->__('Can not activate the quote. It has already been converted to order.'));
                    }
                }

                $quote->setIsActive(1)->save();
                $this->_logger->warn($this->__('Activated quote ' . $quote->getId() . '.'));
                return true;
            }
        }

        return false;
    }

    /**
     * Deactivates the quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function deactivateQuote($quote)
    {
        if ($quote && $quote->getId()) {
            if ($quote->getIsActive()) {
                $quote->setIsActive(0)->save();
                $this->_logger->warn($this->__('Deactivated quote ' . $quote->getId() . '.'));
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if customer exists by email
     *
     * @param string $customer_email
     * @return int
     */
    public function lookupCustomerId($customer_email)
    {
        return Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($customer_email)->getId();
    }

    /**
     * Declines the order
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $customer_email
     */
    public function declineOrder($order, $order_comment = null)
    {
        if ($order) {
            if ($order_comment) {
                $order->addStatusHistoryComment($order_comment)->save();
            }

            $order->setStatus(Zipmoney_ZipmoneyPayment_Model_Config::STATUS_MAGENTO_DECLINED)->save();
        }
    }

    /**
     * Cancels the order
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $customer_email
     */
    public function cancelOrder($order, $order_comment = null)
    {
        if ($order) {
            if ($order_comment) {
                $order->addStatusHistoryComment($order_comment)->save();
            }

            $order->cancel()->save();
        }
    }

    /**
     * Cancels the order
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    public function getTransaction($order_id)
    {
        $transaction = Mage::getModel('sales/order_payment_transaction')->getCollection()->addAttributeToFilter('order_id', array('eq' => $order_id));
        return $transaction;
    }

    /**
     * Retrieves the extension version.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->Zipmoney_ZipmoneyPayment->version;
    }

}
