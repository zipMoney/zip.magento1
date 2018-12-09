<?php


use Zip\Model\CreateChargeRequest as ChargeRequest;
use Zip\Model\CaptureChargeRequest;
use Zip\Model\Authority;
use Zip\Api\ChargesApi;

class Zip_Payment_Model_Api_Charge extends Zip_Payment_Model_Api_Abstract 
{

    const AUTHORITY_TYPE_CHECKOUT = "checkout_id";
    const AUTHORITY_TYPE_TOKEN = "account_token";
    const AUTHORITY_TYPE_STORECODE = "store_code";

    protected $chargeId = null;
    protected $order = null;
    protected $paymentAction = null;

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function setPaymentAction($paymentAction) {
        $this->paymentAction = $paymentAction;
        return $this;
    }

    public function getApi()
    {
        if ($this->api === null) {
            $this->api = new ChargesApi();
        }
        return $this->api;
    }

    /**
     * create charge api
     *
     * @param chargeRequest $payload
     * @return jsonObject
     */
    public function create()
    {
        $payload = $this->preparePayload();

        try {
            $this->getLogger()->log("create charge request:" . json_encode($payload));

            $charge = $this->getApi()->chargesCreate($payload, $this->genIdempotencyKey());

            $this->getLogger()->log("create charge response:" . json_encode($response));

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not create the charge'));
            }

            if (!$charge->getState() || !$charge->getId()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge'));
            }

            $this->getLogger()->log($this->getHelper()->__("Charge State:- %s", $charge->getState()));

            $this->chargeId = $charge->getId();

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
    public function capture($amount)
    {
        try {
            $captureChargeReq = new CaptureChargeRequest();
            $captureChargeReq->setAmount((float)$amount);

            $this->getLogger()->debug("capture charge request:" . json_encode($captureChargeReq));
            $this->response = $this->getApi()->chargesCapture($this->getChargeId(), $captureChargeReq, $this->getIdempotencyKey());
            $this->getLogger()->debug("capture charge response:" . json_encode($response));

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
    public function preparePayload()
    {
        $chargeReq = new ChargeRequest();
        $checkoutId = Mage::getSingleton('core/session')->getZipCheckoutId();

        $chargeReq->setReference($this->order->getIncrementId())
            ->setAmount((float)$this->order->getGrandTotal())
            ->setCurrency($this->order->getOrderCurrencyCode())
            ->setOrder($this->getOrderDetails($this->order))
            ->setMetadata($this->getMetadata())
            ->setCapture($this->isImmediateCapture())
            ->setAuthority($this->getAuthority($checkoutId));

        return $chargeReq;
    }


    /**
     * Returns the prepared authority model
     *
     * @return Zip\Model\Authority
     */
    protected function getAuthority($value, $type = self::AUTHORITY_TYPE_CHECKOUT)
    {
        $authority = new Authority();
        $authority->setType($type)->setValue($value);
        return $authority;
    }

    protected function getIdempotencyKey()
    {
        return uniqid();
    }

    protected function isImmediateCapture() 
    {
        return $this->paymentAction == Zip_Payment_Model_Method::ACTION_AUTHORIZE_CAPTURE;
    }

    protected function getChargeId() {
        return $this->chargeId;
    }
}