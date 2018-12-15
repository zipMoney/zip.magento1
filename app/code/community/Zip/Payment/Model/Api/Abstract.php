<?php

use Zip\Model\OrderShipping;
use Zip\Model\OrderItem;
use Zip\Model\Address;
use Zip\Model\Metadata;
use Zip\ApiException;

abstract class Zip_Payment_Model_Api_Abstract
{
    protected $api = null;
    protected $apiConfig = null;
    protected $logger = null;
    protected $response = null;
    protected $quote = null;

    public function __construct($apiConfig)
    {
        $this->apiConfig = $apiConfig;
        Mage::helper('zip_payment')->autoload();
    }

    abstract protected function getApi();
    abstract protected function prepareCreatePayload();

    /**
     * Get logger object
     * @return Zip_Payment_Model_Logger
     */
    protected function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = Mage::getModel('zip_payment/logger');
        }
        return $this->logger;
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
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->getHelper()->getCheckoutSession()->getQuote();
        }

        return $this->quote;
    }

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
     * Returns the prepared metadata model
     * Dummy data as normal merchant don't need this
     * @return Zip\Model\Metadata
     */
    protected function getMetadata()
    {
        $metadata = new Metadata();
        return $metadata;
    }

    protected function getIdempotencyKey()
    {
        return uniqid();
    }

    protected function getResponse() {
        return $this->response;
    }

    protected function getOrderShipping() {

        $shippingDetail = new OrderShipping();

        $address = $this->getQuote()->getShippingAddress();
        $isPickup = $this->getQuote()->isVirtual() || $address == null;
        $shippingDetail->setPickup($isPickup);

        if (!$isPickup) {

            $shippingAddress = new Address();
            
            $shippingAddress
            ->setFirstName($address->getFirstName())
            ->setLastName($address->getLastName())
            ->setLine1($address->getStreet1())
            ->setLine2($address->getStreet2())
            ->setCountry($address->getCountryId())
            ->setPostalCode($address->getPostcode())
            ->setState(empty($address->getRegion()) ? $address->getCity() : $address->getRegion())
            ->setCity($address->getCity());

            $shippingDetail->setAddress($shippingAddress);

            // $shippingDetail->setTracking() // TODO
        }
        
        return $shippingDetail;
    }

    protected function getOrderItems() {

        $items = $this->getQuote()->getAllVisibleItems();

        $orderItems = array();
        $totalItemAmount = 0.0;

        foreach($items as $item) {

            $product = $item->getProduct();
            $orderItem = new OrderItem();

            $price = (float) $item->getPriceInclTax();
            $quantity = (int) $item->getQty();
            $amount = (float) ($price * $quantity);
            $totalItemAmount += $amount;
            $thumbnailUrl = (string) Mage::helper('catalog/image')->init($product, 'thumbnail');

            $orderItem
            ->setReference((string) $item->getId())
            ->setProductCode((string) $item->getSku())
            ->setName((string) $item->getName())
            ->setDescription((string) $item->getDescription())
            ->setAmount($price)
            ->setQuantity($quantity)
            ->setType('sku')
            ->setItemUri((string) $product->getProductUrl())
            ->setImageUri($thumbnailUrl);

            $orderItems[] = $orderItem;
        }

         //discount and other promotion to balance out
         $shippingAmount = (float) $this->getQuote()->getShippingAddress()->getShippingAmount();

         if ($shippingAmount > 0) {
 
             $shippingItem = new OrderItem;
 
             $shippingItem
             ->setName('Shipping')
             ->setAmount((float) $shippingAmount)
             ->setType('shipping')
             ->setQuantity(1);
 
             $orderItems[] = $shippingItem;
         }
 
         $grandTotal = $this->getQuote()->getGrandTotal() ? $this->getQuote()->getGrandTotal() : 0.00;
         //no matter discount or reward point or store credit
         $remaining = $totalItemAmount + $shippingAmount - $grandTotal;
 
         if ($remaining < 0) {
 
             $discountItem = new OrderItem();
 
             $discountItem
             ->setName("Discount")
             ->setAmount((float) $remaining)
             ->setQuantity(1)
             ->setType("discount");
 
             $orderItems[] = $discountItem;
         }

        return $orderItems;


    }
    
}