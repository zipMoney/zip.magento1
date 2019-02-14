<?php

/**
 * Checkout API Model
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Model\CreateCheckoutRequest;
use \Zip\Api\CheckoutApi;
use \Zip\Model\CheckoutOrder;
use \Zip\Model\Address;
use \Zip\Model\Shopper;
use \Zip\Model\CheckoutConfiguration;
use \Zip\ApiException;

class Zip_Payment_Model_Api_Checkout extends Zip_Payment_Model_Api_Abstract
{

    const CHECKOUT_TYPE = 'standard';
    const CHECKOUT_ID_KEY = 'id';
    const CHECKOUT_REDIRECT_URL_KEY = 'uri';
    const CHECKOUT_STATE_KEY = 'state';

    const STATE_CREATED = 'created';
    const STATE_EXPIRED = 'expired';
    const STATE_REFERRED = 'referred';
    const STATE_APPROVED = 'approved';
    const STATE_COMPLETED = 'completed';
    const STATE_CANCELLED = 'cancelled';
    const STATE_DECLINED = 'declined';

    /**
     * get API model
     *
     * @return Zip\Api\CheckoutApi
     */
    protected function getApi()
    {
        if ($this->api === null) {
            $this->api = new CheckoutApi();
        }

        return $this->api;
    }

    /**
     * create a checkout
     */
    public function create()
    {
        $checkoutId = $this->getHelper()->getCheckoutIdFromSession();

        if (empty($checkoutId)) {
            $payload = $this->prepareCreatePayload();

            try {
                $this->getLogger()->debug("Create checkout");

                $checkout = $this->getApi()->checkoutsCreate($payload);

                if (isset($checkout->error)) {
                    Mage::throwException($this->getHelper()->__('Something wrong when checkout been created'));
                }

                if (isset($checkout[self::CHECKOUT_ID_KEY]) && isset($checkout[self::CHECKOUT_REDIRECT_URL_KEY])) {
                    // save checkout data into session
                    $this->getHelper()->saveCheckoutSessionData(
                        array(
                            self::CHECKOUT_ID_KEY => $checkout[self::CHECKOUT_ID_KEY],
                            self::CHECKOUT_REDIRECT_URL_KEY => $checkout[self::CHECKOUT_REDIRECT_URL_KEY]
                        )
                    );
                } else {
                    throw new Mage_Payment_Exception("Could not create checkout");
                }

                $this->response = $checkout;
            } catch (ApiException $e) {
                $this->logException($e);
                throw $e;
            }
        } else {
            $this->getLogger()->debug("Checkout ID already exists:" . json_encode($checkoutId));
        }

        return $this;
    }

    /**
     * retrieve a checkout
     */
    public function retrieve($checkoutId)
    {
        if (!empty($checkoutId)) {
            try {
                $this->getLogger()->debug("Retrieve checkout");

                $checkout = $this->getApi()->checkoutsGet($checkoutId);

                if (!isset($checkout[self::CHECKOUT_ID_KEY])) {
                    throw new Mage_Payment_Exception("Could not retrieve a checkout");
                }

                $this->response = $checkout;
            } catch (ApiException $e) {
                $this->logException($e);
                throw $e;
            }
        } else {
            $this->getLogger()->debug("Checkout ID does not exist");
        }

        return $this;
    }

    /**
     * prepare for payload of checkout creation
     */
    protected function prepareCreatePayload()
    {
        $checkoutReq = new CreateCheckoutRequest();

        $checkoutReq
            ->setType(self::CHECKOUT_TYPE)
            ->setShopper($this->getShopper())
            ->setOrder($this->getCheckoutOrder())
            ->setMetadata($this->getMetadata())
            ->setConfig($this->getCheckoutConfiguration());

        return $checkoutReq;
    }

    /**
     * generate payload data for customer
     */
    protected function getShopper()
    {
        $shopper = new Shopper();

        $billing = $this->getQuote()->getBillingAddress();
        $shipping = $this->getQuote()->getShippingAddress();

        $billingFirstName = $billing->getFirstname();
        $billingLastName = $billing->getLastname();
        $billingEmail = $billing->getEmail();
        $billingPhone = $billing->getTelephone();

        $firstName = !empty($billingFirstName) ? $billingFirstName : $shipping->getFirstname();
        $lastName = !empty($billingLastName) ? $billingLastName : $shipping->getLastname();
        $email = !empty($billingEmail) ? $billingEmail : $shipping->getEmail();
        $phone = !empty($billingPhone) ? $billingPhone : $shipping->getTelephone();

        $shopper->setEmail($email);
        $shopper->setFirstName($firstName);
        $shopper->setLastName($lastName);
        $shopper->setPhone($phone);

        $billingAddr = new Address();
        $billingAddr->setLine1($billing->getStreet1());
        $billingAddr->setLine2($billing->getStreet2());
        $billingAddr->setCountry($billing->getCountryId());
        $billingAddr->setState($billing->getRegion());
        $billingAddr->setCity($billing->getCity());
        $billingAddr->setPostalCode($billing->getPostcode());

        $shopper->setBillingAddress($billingAddr);
        return $shopper;
    }

    /**
     * generate payload data for order
     */
    protected function getCheckoutOrder()
    {
        $order = new CheckoutOrder();
        $quote = $this->getQuote();

        $reference = $quote->getReservedOrderId() ?: null;
        $currency = $quote->getQuoteCurrencyCode() ?: null;
        $grandTotal = $quote->getGrandTotal() ?: 0.00;

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
        $redirectUrl = $this->getHelper()->getUrl(Zip_Payment_Model_Config::CHECKOUT_RESPONSE_URL_ROUTE);
        $checkoutConfig->setRedirectUri($redirectUrl);

        return $checkoutConfig;
    }

    /**
     * get redirect url
     */
    public function getRedirectUrl()
    {
        return $this->getResponse() ? $this->getResponse()->getUri() : null;
    }

    /**
     * get checkout id
     */
    public function getId()
    {
        return $this->getResponse() ? $this->getResponse()->getId() : null;
    }

    /**
     * get checkout state
     */
    public function getState()
    {
        return $this->getResponse() ? $this->getResponse()->getState() : null;
    }

    /**
     * get all allowed checkout states
     */
    public function getAllowedStates()
    {
        return $this->getResponse() ? $this->getResponse()->getStateAllowableValues() : null;
    }

    /**
     * get order reference
     */
    public function getOrderReference()
    {
        return $this->getResponse() ? $this->getResponse()->getOrder()->getReference() : null;
    }


}
