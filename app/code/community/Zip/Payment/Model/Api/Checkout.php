<?php

use Zip\Model\CreateCheckoutRequest as CheckoutRequest;
use Zip\Api\CheckoutsApi;
use Zip\Model\CheckoutOrder;
use Zip\Model\Shopper;
use Zip\Model\Address;
use Zip\Model\OrderItem;
use Zip\Model\OrderShipping;
use Zip\Model\CheckoutConfiguration;

class Zip_Payment_Model_Api_Checkout extends Zip_Payment_Model_Api_Abstract
{
    protected $quote = null;

    public function setQuote($quote) {
        $this->quote = $quote;
        return $this;
    }

    protected function getApi()
    {
        if ($this->api === null) {
            $this->api = new CheckoutsApi();
        }

        return $this->api;
    }

    public function create()
    {
        $payload = $this->preparePayload();

        try {

            $this->getLogger()->log("create checkout request:" . json_encode($payload));
            $response = $this->getApi()->checkoutsCreate($payload);
            $this->getLogger()->log("create checkout response:" . json_encode($response));

            if (isset($response->error)) {
                Mage::throwException($this->getHelper()->__('Something wrong when checkout been created'));
            }

            if (isset($response['id']) && isset($response['uri'])) {
                $this->getSession()->setZipCheckoutId($response['id']);
            } else {
                throw new Mage_Payment_Exception("Could not redirect to zip checkout page");
            }

            $this->response = $response;

        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    protected function preparePayload()
    {
        $checkoutReq = new CheckoutRequest();

        $checkoutReq->setType("standard")
            ->setShopper($this->getShopper())
            ->setOrder($this->getCartDetail())
            ->setMetadata($this->getMetadata())
            ->setConfig($this->getCheckoutConfiguration());

        return $checkoutReq;
    }

    protected function getShopper()
    {
        $shopper = new Shopper();

        $billing = $this->quote->getBillingAddress();
        $shipping = $this->quote->getShippingAddress();

        $firstName = !empty($billing->getFirstname()) ? $billing->getFirstname() : $shipping->getFirstname();
        $lastName = !empty($billing->getLastname()) ? $billing->getLastname() : $shipping->getLastname();
        $email = !empty($billing->getEmail()) ? $billing->getEmail() : $shipping->getEmail();
        $phone = !empty($billing->getTelephone()) ? $billing->getTelephone() : $shipping->getTelephone();

        $shopper->setEmail($email);
        $shopper->setFirstName($firstName);
        $shopper->setLastName($lastName);
        $shopper->setPhone($phone);

        $billingAddr = new Address();
        $billingAddr->setLine1($billing->getStreet1());
        $billingAddr->setLine2($billing->getStreet2());
        $billingAddr->setCountry($billing->getCountryId());
        $billingAddr->setState(empty($billing->getRegion()) ? $billing->getCity() : $billing->getRegion());
        $billingAddr->setCity($billing->getCity());
        $billingAddr->setPostalCode($billing->getPostcode());

        $shopper->setBillingAddress($billingAddr);
        return $shopper;
    }

    protected function getCartDetail()
    {
        $order = new CheckoutOrder();
        $reference = $this->quote->getReservedOrderId();
        $currency = $this->quote->getQuoteCurrencyCode() ? $this->quote->getQuoteCurrencyCode() : null;
        $items = $this->quote->getAllVisibleItems();
        $orderItems = array();
        $totalItemPrice = 0.0;

        foreach ($items as $item) {
            $orderItem = new OrderItem();
            $orderItem->setName($item->getName());
            $orderItem->setReference($item->getSku());
            $orderItem->setQuantity((int) $item->getQty());

            $price = (float) $item->getPriceInclTax();
            $totalItemPrice += $price;

            $orderItem->setAmount($price);
            $orderItem->setType("sku");

            $orderItems[] = $orderItem;
        }

        //discount and other promotion to balance out
        $shippingAmount = (float) $this->quote->getShippingAddress()->getShippingAmount();
        if ($shipping_amount > 0) {
            $shippingItem = new OrderItem;
            $shippingItem->setName("Shipping");
            $shippingItem->setAmount((float) $shipping_amount);
            $shippingItem->setType("shipping");
            $shippingItem->setQuantity(1);
            $orderItems[] = $shippingItem;
        }

        $grandTotal = $this->quote->getGrandTotal() ? $this->quote->getGrandTotal() : 0.00;
        //no matter discount or reward point or store credit
        $remaining = $totalItemPrice + $shippingAmount - $grandTotal;
        if ($remaining < 0) {
            $discountItem = new OrderItem();
            $discountItem->setName("Discount");
            $discountItem->setAmount((float) $remaining);
            $discountItem->setQuantity(1);
            $discountItem->setType("discount");
            $orderItems[] = $discountItem;
        }

        $shippingDetail = new OrderShipping();
        if ($this->quote->isVirtual()) {
            $shippingDetail->setPickup = true;
        } else {
            $shippingDetail->setPickup = false;

            $shippingAddress = new Address();
            $address = $this->quote->getShippingAddress();

            $shippingAddress->setFirstName($address->getFirstName());
            $shippingAddress->setLastName($address->getLastName());
            $shippingAddress->setLine1($address->getStreet1());
            $shippingAddress->setLine2($address->getStreet2());
            $shippingAddress->setCountry($address->getCountryId());
            $shippingAddress->setPostalCode($address->getPostcode());
            $shippingAddress->setState(empty($address->getRegion()) ? $address->getCity() : $address->getRegion());
            $shippingAddress->setCity($address->getCity());

            $shippingDetail->setAddress($shippingAddress);
        }

        $order->setReference($reference);
        $order->setShipping($shippingDetail);
        $order->setCurrency($currency);
        $order->setAmount($grandTotal);
        $order->setItems($orderItems);

        return $order;
    }

     /**
     * Returns the prepared checkout configuration model
     *
     * @return Zip\Model\CheckoutConfiguration
     */
    protected function getCheckoutConfiguration()
    {
        $checkoutConfig = new CheckoutConfiguration();
        $redirectUrl = Mage::helper('zip_payment')->getUrl(Zip_Payment_Model_Config::CHECKOUT_RESPONSE_URL_ROUTE);
        $checkoutConfig->setRedirectUri($redirectUrl);

        return $checkoutConfig;
    }

    public function getRedirectUrl() {

        if($this->getResponse()) {
            return $this->getResponse()->getUri();
        }

        return null;
        
    }
}