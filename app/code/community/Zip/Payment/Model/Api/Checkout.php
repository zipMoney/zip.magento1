<?php

use Zip\Model\CreateCheckoutRequest;
use Zip\Api\CheckoutApi;
use Zip\Model\CheckoutOrder;
use Zip\Model\Shopper;
use Zip\Model\Address;
use Zip\Model\CheckoutConfiguration;
use Zip\ApiException;

class Zip_Payment_Model_Api_Checkout extends Zip_Payment_Model_Api_Abstract
{

    protected function getApi()
    {
        if ($this->api === null) {
            $this->api = new CheckoutApi();
        }

        return $this->api;
    }

    public function create()
    {
        $payload = $this->prepareCreatePayload();

        try {

            $this->getLogger()->debug("create checkout request:" . json_encode($payload));

            $checkout = $this->getApi()->checkoutsCreate($payload);
            
            $this->getLogger()->debug("create checkout response:" . json_encode($checkout));

            if (isset($checkout->error)) {
                Mage::throwException($this->getHelper()->__('Something wrong when checkout been created'));
            }

            if (isset($checkout['id']) && isset($checkout['uri'])) {
                $this->getHelper()->setCheckoutSessionId($checkout['id']);
            } else {
                throw new Mage_Payment_Exception("Could not redirect to zip checkout page");
            }

            $this->response = $checkout;

        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    protected function prepareCreatePayload()
    {
        $checkoutReq = new CreateCheckoutRequest();

        $checkoutReq
        ->setType('standard')
        ->setShopper($this->getShopper())
        ->setOrder($this->getCheckoutOrder())
        ->setMetadata($this->getMetadata())
        ->setConfig($this->getCheckoutConfiguration());

        return $checkoutReq;
    }

    protected function getShopper()
    {
        $shopper = new Shopper();

        $billing = $this->getQuote()->getBillingAddress();
        $shipping = $this->getQuote()->getShippingAddress();

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

    protected function getCheckoutOrder()
    {
        $order = new CheckoutOrder();

        $reference = $this->getQuote()->getReservedOrderId();
        $currency = $this->getQuote()->getQuoteCurrencyCode() ? $this->getQuote()->getQuoteCurrencyCode() : null;
        $grandTotal = $this->getQuote()->getGrandTotal() ? $this->getQuote()->getGrandTotal() : 0.00;

        $order
        ->setReference($reference)
        ->setShipping($this->getOrderShipping())
        ->setCurrency($currency)
        ->setAmount($grandTotal)
        ->setItems($this->getOrderItems());

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
        return $this->getResponse() ? $this->getResponse()->getUri() : null;
    }
}