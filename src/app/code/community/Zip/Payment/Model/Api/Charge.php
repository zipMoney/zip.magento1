<?php

/**
 * Charge API Model
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Model\CreateChargeRequest;
use \Zip\Model\CaptureChargeRequest;
use \Zip\Model\ChargeOrder;
use \Zip\Model\Authority;
use \Zip\Api\ChargeApi;
use \Zip\ApiException;

class Zip_Payment_Model_Api_Charge extends Zip_Payment_Model_Api_Abstract
{

    const AUTHORITY_TYPE_CHECKOUT = "checkout_id";
    const AUTHORITY_TYPE_TOKEN = "account_token";
    const AUTHORITY_TYPE_STORECODE = "store_code";

    protected $_paymentAction = null;

    /**
     * get API model
     *
     * @return Zip\Api\ChargeApi
     */
    public function getApi()
    {
        if ($this->_api === null) {
            $this->_api = new ChargeApi();
        }

        return $this->_api;
    }

    /**
     * Create charge api
     *
     * @param  CreateChargeRequest $payload
     * @return jsonObject
     */
    public function create($order, $paymentAction)
    {
        $this->_order = $order;
        $this->_paymentAction = $paymentAction;

        $payload = $this->prepareCreatePayload();

        try {
            $charge = $this->getApi()
                ->chargesCreate($payload, $this->getIdempotencyKey());

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not create the charge'));
            }

            if (!$charge->getState() || !$charge->getId()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge'));
            }

            $this->getLogger()->log($this->getHelper()->__("Charge State: %s", $charge->getState()));

            $this->_response = $charge;
        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Charge the previous authorized checkout
     *
     * @param  string $chargeId
     * @param  float  $amount
     * @return jsonObject
     */
    public function capture($chargeId, $amount, $isPartialCapture = null)
    {
        try {
            $params = array(
                'amount' => (float) $amount,
                'is_partial_capture' => $isPartialCapture
            );

            $captureChargeReq = new CaptureChargeRequest($params);

            $this->getLogger()->debug("Create charge: " . json_encode($params));

            $charge = $this->getApi()
                ->chargesCapture($chargeId, $captureChargeReq, $this->getIdempotencyKey());

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not capture the charge'));
            }

            if (!$charge->getState()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge'));
            }

            $this->getLogger()->debug('Charge State: ' . $charge->getState());

            $this->_response = $charge;
        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    /**
     * cancel a charge
     */
    public function cancel($chargeId)
    {
        try {
            $this->getLogger()->debug('Cancel charge: '. $chargeId);

            $charge = $this->getApi()
                ->chargesCancel($chargeId, $this->getIdempotencyKey());

            if (isset($charge->error)) {
                Mage::throwException($this->getHelper()->__('Could not capture the charge'));
            }

            if (!$charge->getState()) {
                Mage::throwException($this->getHelper()->__('Invalid Charge Cancel'));
            }

            $this->getLogger()->debug('Charge State: ' . $charge->getState());

            $this->_response = $charge;
        } catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }


    /**
     * charge and authorized are the same payload
     */
    public function prepareCreatePayload()
    {
        $chargeReq = new CreateChargeRequest();

        $chargeReq
            ->setReference((string) $this->getOrder()->getIncrementId())
            ->setAmount((float) $this->getOrder()->getGrandTotal())
            ->setCurrency((string) $this->getOrder()->getOrderCurrencyCode())
            ->setOrder($this->getChargeOrder())
            ->setMetadata($this->getMetadata())
            ->setCapture($this->isImmediateCapture())
            ->setAuthority($this->getAuthority());

        return $chargeReq;
    }

    /**
     * Prepare charge order
     *
     * @return ChargeOrder
     */
    protected function getChargeOrder()
    {
        $chargeOrder = new ChargeOrder();

        $chargeOrder
            ->setReference((string) $this->getOrder()->getIncrementId())
            ->setShipping($this->getOrderShipping())
            ->setItems($this->getOrderItems())
            ->setCartReference((string) $this->getOrder()->getId());

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
            ->setValue($this->getHelper()->getCheckoutIdFromSession());

        return $authority;
    }

    /**
     * is currently using immediate capture
     */
    protected function isImmediateCapture()
    {
        return $this->_paymentAction == Zip_Payment_Model_Method::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * get charge id
     */
    public function getId()
    {
        return $this->getResponse() ? $this->getResponse()->getId() : null;
    }

    /**
     * get receipt number
     */
    public function getReceiptNumber()
    {
        return $this->getResponse() ? $this->getResponse()->getReceiptNumber() : null;
    }
}
