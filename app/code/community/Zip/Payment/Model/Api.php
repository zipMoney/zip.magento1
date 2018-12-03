<?php
use \zipMoney\Model\CreateCheckoutRequest as CheckoutRequest;
use \zipMoney\Model\CreateChargeRequest as ChargeRequest;
use \zipMoney\Model\CreateRefundRequest as RefundRequest;
use \zipMoney\Model\CaptureChargeRequest;
use \zipMoney\Model\Shopper;
use \zipMoney\Model\CheckoutOrder;
use \zipMoney\Model\ChargeOrder;
use \zipMoney\Model\Authority;
use \zipMoney\Model\OrderShipping;
use \zipMoney\Model\OrderShippingTracking;
use \zipMoney\Model\Address;
use \zipMoney\Model\OrderItem;
use \zipMoney\Model\Metadata;
use \zipMoney\Model\CheckoutConfiguration;
use \zipMoney\ApiException;

class Zip_Payment_Model_Api
{
    const AUTHORITY_TYPE_CHECKOUT = "checkout_id";
    const AUTHORITY_TYPE_TOKEN = "account_token";
    const AUTHORITY_TYPE_STORECODE = "store_code";

    protected $logger = null;
    protected $chargeApi = null;
    protected $checkoutApi = null;

    public function __construct()
    {
        //initial the API SDK here only
        if (!class_exists('\zipMoney\ApiClient', false)) {
            include_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';
        }

        $this->logger = new Zip_Payment_Model_Logger();
    }

    public function getCheckoutApi()
    {
        if ($this->checkoutApi === null) {
            $this->checkoutApi = new \zipMoney\Api\CheckoutsApi();
        }

        return $this->checkoutApi;
    }

    public function getChargeApi()
    {
        if ($this->chargeApi === null) {
            $this->chargeApi = new \zipMoney\Api\ChargesApi();
        }

        return $this->chargeApi;
    }

    /**
     * Returns the prepared metadata model
     * Dummy data as normal merchant don't need this
     * @return \zipMoney\Model\Metadata
     */
    public function getMetadata()
    {
        $metadata = new Metadata();
        return $metadata;
    }

    public function genIdempotencyKey()
    {
        return uniqid();
    }

    /**
     * Returns the prepared authority model
     *
     * @return \zipMoney\Model\Authority
     */
    public function getAuthority($value, $type = self::AUTHORITY_TYPE_CHECKOUT)
    {
        $authority = new Authority();
        $authority->setType($type)
            ->setValue($value);
        return $authority;
    }

    /**
     * charge and authorized are the same payload
     *
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @param \zipMoney\Model\Authority $authority
     * @param boolean $isCharge
     * @return \zipMoney\Model\CreateChargeRequest $chargeReq
     */
    public function prepareChargeData($order, $amount, $authority, $isCharge = true)
    {
        $chargeReq = new ChargeRequest();
        $orderRef = $order->getIncrementId();
        $currency = $order->getOrderCurrencyCode() ? $order->getOrderCurrencyCode() : null;

        $chargeReq->setReference($orderRef)
            ->setAmount((float) $amount)
            ->setCurrency($currency)
            ->setOrder($this->getOrderDetails($order))
            ->setMetadata($this->getMetadata())
            ->setCapture($isCharge)
            ->setAuthority($authority);

        return $chargeReq;
    }

    /**
     * Charge the previous authorized
     *
     * @param string $chargeId
     * @param float $amount
     * @return jsonObject
     */
    public function captureCharge($chargeId, $amount)
    {
        try {
            $chargeApi = $this->getChargeApi();
            $captureChargeReq = new CaptureChargeRequest();
            $captureChargeReq->setAmount((float) $amount);
            $this->logger->debug("capture charge request:" . json_encode($captureChargeReq));
            $response = $chargeApi->chargesCapture($chargeId, $captureChargeReq, $this->genIdempotencyKey());
            $this->logger->debug("capture charge response:" . json_encode($response));
            return $response;
        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }
    }

    /**
     * create charge api
     *
     * @param chargeRequest $payload
     * @return jsonObject
     */
    public function createCharge($payload)
    {
        try {
            $this->logger->debug("create charge request:" . json_encode($payload));
            $chargeApi = $this->getChargeApi();
            $response = $chargeApi->chargesCreate($payload, $this->genIdempotencyKey());
            $this->logger->debug("create charge response:" . json_encode($response));
            return $response;
        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }
    }

    public function getShopper($quote)
    {
        $shopper = new Shopper();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();
        $firstname = !empty($billing->getFirstname()) ? $billing->getFirstname() : $shipping->getFirstname();
        $lastname = !empty($billing->getLastname()) ? $billing->getLastname() : $shipping->getLastname();
        $email = !empty($billing->getEmail()) ? $billing->getEmail() : $shipping->getEmail();
        $phone = !empty($billing->getTelephone()) ? $billing->getTelephone() : $shipping->getTelephone();

        $shopper->setEmail($email);
        $shopper->setFirstName($firstname);
        $shopper->setLastName($lastname);
        $shopper->setPhone($phone);
        $billingAddr = new Address();
        $billingAddr->setLine1($billing->getStreet1());
        $billingAddr->setLine2($billing->getStreet2());
        $billingAddr->setCountry($billing->getCountryId());
        $billingAddr->setCity($billing->getCity());
        $billingAddr->setState($billing->getRegion());
        $billingAddr->setPostalCode($billing->getPostcode());

        $shopper->setBillingAddress($billingAddr);
        return $shopper;
    }

    public function getCartDetail($quote)
    {
        $order = new CheckoutOrder();
        $reference = $quote->getReservedOrderId();
        $currency = $quote->getQuoteCurrencyCode() ? $quote->getQuoteCurrencyCode() : null;
        $items = $quote->getAllVisibleItems();
        $orderItems = array();
        $totalItemPrice = 0.0;
        foreach ($items as $item) {
            $orderItem = new OrderItem();
            $orderItem->setName($item->getName());
            $orderItem->setReference($item->getSku());
            $orderItem->setQuantity((int) $item->getQtyOrdered());
            $price = (float) $item->getPriceInclTax();
            $totalItemPrice += $price;
            $orderItem->setAmount($price);
            $orderItem->setType("sku");
            $orderItems[] = $orderItem;
        }

        //discount and other promotion to balance out
        $shippingAmount = (float) $quote->getShippingAddress()->getShippingAmount();
        if ($shipping_amount > 0) {
            $shippingItem = new OrderItem;
            $shippingItem->setName("Shipping");
            $shippingItem->setAmount((float) $shipping_amount);
            $shippingItem->setType("shipping");
            $shippingItem->setQuantity(1);
            $orderItems[] = $shippingItem;
        }

        $grandTotal = $quote->getGrandTotal() ? $quote->getGrandTotal() : 0.00;
        //no matter discount or reward point or store credit
        $remaining = $totalItemPrice + $shippingAmount - $grandTotal;
        if ($remaining < 0) {
            $discountItem = new OrderItem;
            $discountItem->setName("Discount");
            $discountItem->setAmount((float) $remaining);
            $discountItem->setQuantity(1);
            $discountItem->setType("discount");
            $orderItems[] = $discountItem;
        }

        $shippingDetail = new OrderShipping();
        if ($quote->isVirtual()) {
            $shippingDetail->setPickup = true;
        } else {
            $shippingDetail->setPickup = false;
            $reqAddress = new Address;
            $address = $quote->getShippingAddress();
            $reqAddress->setFirstName($address->getFirstname());
            $reqAddress->setLastName($address->getLastname());
            $reqAddress->setLine1($address->getStreet1());
            $reqAddress->setLine2($address->getStreet2());
            $reqAddress->setCountry($address->getCountryId());
            $reqAddress->setPostalCode($address->getPostcode());
            $reqAddress->setCity($address->getCity());
        }

        $order->setReference($reference);
        $order->setShipping($shippingDetail);
        $order->setCurrency($currency);
        $order->setAmount($grandTotal);
        $order->setItems($orderItems);

        return $order;
    }

    public function prepareCheckoutData($quote)
    {
        $checkoutReq = new CheckoutRequest();

        $checkoutReq->setType("standard")
            ->setShopper($this->getShopper($quote))
            ->setOrder($this->getCartDetail($quote))
            ->setMetadata($this->getMetadata())
            ->setConfig($this->getCheckoutConfiguration());
        return $checkoutReq;
    }

    public function createCheckout($payload)
    {
        try {
            $this->logger->debug("create checkout request:" . json_encode($payload));
            $checkoutApi = $this->getCheckoutApi();
            $response = $checkoutApi->checkoutsCreate($payload);
            $this->logger->debug("create checkout response:" . json_encode($response));
            return $response;
        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }
    }

    protected function logException($e)
    {
        if ($e instanceof ApiException) {
            $message = $e->getMessage();
            $this->logger->debug("Api Error: " . $message);
            $respBody = $e->getResponseBody();
            if ($respBody) {
                $detail = json_encode($respBody);
                $this->logger->debug($detail);
            }
        }
    }
}
