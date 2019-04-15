<?php

/**
 * Refund API Model
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Model\CreateRefundRequest;
use \Zip\Api\RefundApi;
use \Zip\ApiException;

class Zip_Payment_Model_Api_Refund extends Zip_Payment_Model_Api_Abstract
{

    /**
     * get API model
     *
     * @return Zip\Api\RefundApi
     */
    protected function getApi()
    {
        if ($this->_api === null) {
            $this->_api = new RefundApi();
        }

        return $this->api;
    }

    /**
     * create a refund
     *
     * @param string $chargeId Charge ID
     * @param string $amount   Charge Amount
     * @param string $reason   reason of refund
     */
    public function create($chargeId, $amount, $reason)
    {
        $payload = $this->prepareCreatePayload($chargeId, $amount, $reason);

        try {
            $this->getLogger()->debug(
                "Create refund" . json_encode(
                    array(
                    'charge_id' => $chargeId,
                    'amount' => $amount,
                    'reason' => $reason
                    )
                )
            );

            $refund = $this->getApi()->refundsCreate($payload, $this->getIdempotencyKey());

            if (isset($refund->error)) {
                Mage::throwException($this->getHelper()->__('Could not create the refund'));
            }

            if (!$refund->getId()) {
                Mage::throwException($this->getHelper()->__('Invalid Refund'));
            }

            $this->_response = $refund;
        }catch (ApiException $e) {
            $this->logException($e);
            throw $e;
        }

        return $this;
    }

    /**
     * generate payload for refund
     */
    protected function prepareCreatePayload($chargeId, $amount, $reason)
    {
        $refundReq = new CreateRefundRequest();

        $refundReq
            ->setAmount($amount)
            ->setReason($reason)
            ->setChargeId($chargeId)
            ->setMetadata($this->getMetadata());

        return $refundReq;
    }

    /**
     * get refund id
     */
    public function getId()
    {
        return $this->getResponse() ? $this->getResponse()->getId() : null;
    }

}
