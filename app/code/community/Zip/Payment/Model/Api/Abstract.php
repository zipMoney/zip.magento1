<?php

/**
 * Abstract Model of Payment API
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Model\OrderShipping;
use \Zip\Model\OrderItem;
use \Zip\Model\Address;
use \Zip\ApiException;

abstract class Zip_Payment_Model_Api_Abstract
{
    protected $_api = null;
    protected $_apiConfig = null;
    protected $_logger = null;
    protected $_response = null;
    protected $_order = null;
    protected $_quote = null;
    protected $_storeId = null;

    public function __construct($options)
    {
        if (isset($options['store_id']) && !empty($options['store_id'])) {
            $storeId = $options['store_id'];
        } else {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->_apiConfig === null || $this->_storeId !== $storeId) {
            // when api configuration is null or store id has been changed
            // new api configuration need to be created
            $this->_apiConfig = Mage::getSingleton('zip_payment/api_configuration')->generateApiConfiguration($storeId);
            $this->_storeId = $storeId;
        }
    }

    abstract protected function getApi();

    /**
     * Get logger object
     *
     * @return Zip_Payment_Model_Logger
     */
    protected function getLogger()
    {
        if ($this->_logger == null) {
            $this->_logger = Mage::getModel('zip_payment/logger');
        }

        return $this->_logger;
    }

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('zip_payment');
    }

    /**
     * get current order
     */
    protected function getOrder()
    {
        return $this->_order;
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        if ($this->_quote === null) {
            $this->_quote = $this->getHelper()->getCheckoutSession()->getQuote();
        }

        return $this->_quote;
    }

    /**
     * log exception for api error
     */
    protected function logException($e)
    {
        if ($e instanceof ApiException) {
            $message = $e->getMessage();
            $this->getLogger()->error("Api Error: " . $message);
            $respBody = $e->getResponseBody();

            if ($respBody) {
                $detail = json_encode($respBody);
                $this->getLogger()->error($detail);
            }
        }
    }

    /**
     * capture order's shipping details
     *
     * @return Zip\Model\OrderShipping
     */
    protected function getOrderShipping()
    {
        $model = $this->getOrder() ?: $this->getQuote();

        $shippingDetail = new OrderShipping();

        $address = $model->getShippingAddress();

        $shippingAmount = (float) ($this->getOrder() ? $model->getShippingInclTax() : $address->getShippingAmount());
        $isPickup = $shippingAmount > 0 ? false : $this->getHelper()->isPickupOrder($model);
        $shippingDetail->setPickup($isPickup);
        $region = $address->getRegion();

        if (!$isPickup) {
            $shippingAddress = new Address();

            $shippingAddress
                ->setFirstName($address->getFirstName())
                ->setLastName($address->getLastName())
                ->setLine1($address->getStreet1())
                ->setLine2($address->getStreet2())
                ->setCountry($address->getCountryId())
                ->setPostalCode($address->getPostcode())
                ->setState(empty($region) ? $address->getCity() : $region)
                ->setCity($address->getCity());

            $shippingDetail->setAddress($shippingAddress);
        }

        return $shippingDetail;
    }

    /**
     * get order items
     *
     * @return Zip\Model\OrderItem
     */
    protected function getOrderItems()
    {
        $model = $this->getOrder() ?: $this->getQuote();

        $items = $model->getAllVisibleItems();
        $orderItems = array();
        $totalItemAmount = 0.0;

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                // Only sends parent items to zip
                continue;
            }

            $product = $item->getProduct();
            $orderItem = new OrderItem();

            $price = (float) $item->getPriceInclTax();
            $quantity = (int) ($this->getOrder() ? $item->getQtyOrdered() : $item->getQty());
            $amount = (float) ($price * $quantity);
            $totalItemAmount += $amount;
            $thumbnailUrl = (string) Mage::helper('catalog/image')->init($product, 'thumbnail');

            $orderItem
                ->setReference((string) $item->getId())
                ->setProductCode((string) $item->getSku())
                ->setName((string) $item->getName())
                ->setDescription((string) strip_tags($item->getDescription()))
                ->setAmount($price)
                ->setQuantity($quantity)
                ->setType(OrderItem::TYPE_SKU)
                ->setItemUri((string) $product->getProductUrl())
                ->setImageUri($thumbnailUrl);

            $orderItems[] = $orderItem;
        }

        //discount and other promotion to balance out
        $shippingAmount = (float) ($this->getOrder() ? $model->getShippingInclTax() : $model->getShippingAddress()->getShippingAmount());

        if ($shippingAmount > 0) {
            $shippingItem = new OrderItem;

            $shippingItem
                ->setName('Shipping')
                ->setAmount((float) $shippingAmount)
                ->setType(OrderItem::TYPE_SHIPPING)
                ->setQuantity(1);

            $orderItems[] = $shippingItem;
        }

        $grandTotal = $model->getGrandTotal() ?: 0.00;

        // no matter discount or reward point or store credit
        $remaining = $grandTotal - $totalItemAmount - $shippingAmount;

        // Add fee or discount when remaining is not 0
        if ($remaining > 0) {
            $remainingItem = new OrderItem;
            $remainingItem
                ->setName('Fee')
                ->setAmount((float) $remaining)
                ->setQuantity(1)
                ->setType(OrderItem::TYPE_SHIPPING);
            $orderItems[] = $remainingItem;
        } else if ($remaining < 0) {
            $remainingItem = new OrderItem;
            $remainingItem
                ->setName('Discount')
                ->setAmount((float) $remaining)
                ->setQuantity(1)
                ->setType(OrderItem::TYPE_DISCOUNT);
            $orderItems[] = $remainingItem;
        }

        return $orderItems;
    }


    /**
     * Returns the prepared metadata model
     * Dummy data as normal merchant don't need this
     *
     * @return Zip\Model\Metadata
     */
    protected function getMetadata()
    {
        // object not working must use array
        $metadata['platform'] = 'Magento 1';
        $metadata['platform_version'] = Mage::getVersion();
        $metadata['plugin'] = 'zip-magento1';
        $metadata['plugin_version'] = Zip_Payment_Model_Config::VERSION;
        return $metadata;
    }

    /**
     * Get Idempotency Key
     *
     * @return string
     */
    protected function getIdempotencyKey($id = '')
    {
        if ($id !== '' && $id !== null) {
            return sha1($id);
        }

        return uniqid();
    }

    /**
     * Get api response
     *
     * @return object
     */
    protected function getResponse()
    {
        return $this->_response;
    }
}
