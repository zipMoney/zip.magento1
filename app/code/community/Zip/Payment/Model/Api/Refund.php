<?php

use Zip\Model\CreateRefundRequest;
use Zip\Api\RefundApi;
use Zip\ApiException;

class Zip_Payment_Model_Api_Refund extends Zip_Payment_Model_Api_Abstract 
{
    protected $amount = 0.0;
    protected $chargeId = null;
    protected $reason = null;

    protected function getApi()
    {
        if ($this->api === null) {
            $this->api = new RefundApi();
        }

        return $this->api;
    }

    public function create($chargeId, $amount, $reason)
    {
        $this->chargeId = $chargeId;
        $this->amount = $amount;
        $this->reason = $reason;

        $payload = $this->prepareCreatePayload();

        try {

            $this->getLogger()->debug("create refund request:" . json_encode($payload));

            $refund = $this->getApi()->refundsCreate($payload, $this->getIdempotencyKey());
            
            $this->getLogger()->debug("create refund response:" . json_encode($refund));

            if (isset($refund->error)) {
                Mage::throwException($this->_helper->__('Could not create the refund'));
            }

            if (!$refund->getId()) {
                Mage::throwException($this->_helper->__('Invalid Refund'));
            }

            $this->response = $refund;

        }catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    protected function prepareCreatePayload()
    {
        $refundReq = new CreateRefundRequest();

        $refundReq
        ->setAmount($this->getAmount())
        ->setReason($this->getReason())
        ->setChargeId($this->getChargeId())
        ->setMetadata($this->getMetadata());

        return $refundReq;
    }

    protected function getAmount() {
        return $this->amount;
    }

    protected function getChargeId() {
        return $this->chargeId;
    }


    protected function getReason() {
        return $this->reason;
    }

    public function getId() {
        return $this->getResponse() ? $this->getResponse()->getId() : null;
    }
   
}