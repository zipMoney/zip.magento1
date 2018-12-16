<?php


use Zip\Model\CreateChargeRequest;
use Zip\Model\CaptureChargeRequest;
use Zip\Model\ChargeOrder;
use Zip\Model\Authority;
use Zip\Api\ChargeApi;
use Zip\ApiException;

class Zip_Payment_Model_Api_Charge extends Zip_Payment_Model_Api_Abstract 
{

    const AUTHORITY_TYPE_CHECKOUT = "checkout_id";
    const AUTHORITY_TYPE_TOKEN = "account_token";
    const AUTHORITY_TYPE_STORECODE = "store_code";

    protected $chargeId = null;
    protected $receiptNumber = null;

    protected $order = null;
    protected $paymentAction = null;

    protected function getOrder() {
        return $this->order;
    }

    public function getApi()
    {
        if ($this->api === null) {
            $this->api = new ChargeApi();
        }
        return $this->api;
    }

    /**
     * create charge api
     *
     * @param CreateChargeRequest $payload
     * @return jsonObject
     */
    public function create($order, $paymentAction)
    {
        $this->order = $order;
        $this->paymentAction = $paymentAction;

        $payload = $this->prepareCreatePayload();

        try {
            $this->getLogger()->debug("create charge request:" . json_encode($payload));

            $charge = $this->getApi()
            ->chargesCreate($payload, $this->getIdempotencyKey());

            $this->getLogger()->debug("create charge response:" . json_encode($charge));

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not create the charge'));
            }

            if (!$charge->getState() || !$charge->getId()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge'));
            }

            $this->getLogger()->log($this->getHelper()->__("Charge State: %s", $charge->getState()));

            $this->response = $charge;

        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Charge the previous authorized checkout
     *
     * @param string $chargeId
     * @param float $amount
     * @return jsonObject
     */
    public function capture($chargeId, $amount, $isPartialCapture = null)
    {
        try {
            $captureChargeReq = new CaptureChargeRequest(array(
                'amount' => (float)$amount,
                'is_partial_capture' => $isPartialCapture
            ));

            $this->getLogger()->debug("capture charge request:" . json_encode($captureChargeReq));

            $charge = $this->getApi()
            ->chargesCapture($chargeId, $captureChargeReq, $this->getIdempotencyKey());

            $this->getLogger()->debug("capture charge response:" . json_encode($charge));

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not capture the charge'));
            }

            if (!$charge->getState()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge'));
            }

            $this->getLogger()->debug($this->getHelper()->__("Charge State: %s", $charge->getState()));

            $this->response = $charge;

        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    public function cancel($chargeId)
    {
        try {

            $this->getLogger()->debug("cancel charge request: " . $chargeId);

            $charge = $this->getApi()
            ->chargesCancel($chargeId, $this->getIdempotencyKey());

            $this->getLogger()->debug("cancel charge response:" . json_encode($charge));

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not capture the charge'));
            }

            if (!$charge->getState()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge Cancel'));
            }

            $this->getLogger()->debug($this->getHelper()->__("Charge State: %s", $charge->getState()));

            $this->response = $charge;

        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }


    /**
     * charge and authorized are the same payload
     *
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @param Zip\Model\Authority $authority
     * @param boolean $isCharge
     * @return Zip\Model\CreateChargeRequest $chargeReq
     */
    public function prepareCreatePayload()
    {
        $chargeReq = new CreateChargeRequest();

        $chargeReq
        ->setReference((string)$this->getOrder()->getIncrementId())
        ->setAmount((float)$this->getOrder()->getGrandTotal())
        ->setCurrency((string)$this->getOrder()->getOrderCurrencyCode())
        ->setOrder($this->getChargeOrder())
        ->setMetadata($this->getMetadata())
        ->setCapture($this->isImmediateCapture())
        ->setAuthority($this->getAuthority());

        return $chargeReq;
    }

    protected function getChargeOrder()
    {
        $chargeOrder = new ChargeOrder();

        $chargeOrder
        ->setReference((string)$this->getOrder()->getIncrementId())
        ->setShipping($this->getOrderShipping())
        ->setItems($this->getOrderItems())
        ->setCartReference((string)$this->getOrder()->getId());

        return $chargeOrder;

    }


    /**
     * Returns the prepared authority model
     *
     * @return Zip\Model\Authority
     */
    protected function getAuthority($type = self::AUTHORITY_TYPE_CHECKOUT)
    {
        $authority = new Authority();

        $authority
        ->setType($type)
        ->setValue($this->getHelper()->getCheckoutSessionId());

        return $authority;
    }

    protected function isImmediateCapture() 
    {
        return $this->paymentAction == Zip_Payment_Model_Method::ACTION_AUTHORIZE_CAPTURE;
    }

    public function getId() {
       return $this->getResponse() ? $this->getResponse()->getId() : null;
    }

    public function getReceiptNumber() {
        return $this->getResponse() ? $this->getResponse()->getReceiptNumber() : null;
    }
}