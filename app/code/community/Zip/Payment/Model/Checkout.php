<?php

class Zip_Payment_Model_Checkout {

    /**
     * @var Zip_Payment_Model_Logger
     */
    protected $logger = null;

    /**
     * @var Zip_Payment_Model_config
     */
    protected $config = null;

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
     * Get logger object
     * @return Zip_Payment_Model_Logger
     */
    protected function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = Mage::getSingleton('zip_payment/logger');
        }
        return $this->logger;
    }

    /**
     * @return Zip_Payment_Model_config
     */
    protected function getConfig() {

        if($this->config === null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }

        return $this->config;
    }


    public function processResponseResult($result) {

        $response = array(
            'success' => false,
            'error_message' => null
        );

        if($result == Zip_Payment_Model_Api_CheckoutResponseResult::APPROVED) {

            try {

                $this->saveOrder();
                $response['success'] = true;
                
            } catch (Exception $e) {
                throw $e;
            }

        } else {
            $response['error_message'] = $this->generateErrorMessage($result);
        }

        return $response;
    }

    protected function saveOrder() {

        $onepage = $this->getHelper()->getOnepage();
        $onepage->getQuote()->collectTotals();
        $onepage->saveOrder();

        $this->getLogger()->debug('Order has been saved successfully');
    }

    protected function generateErrorMessage($result) {

        $errorMessage = $this->getHelper()->__('Checkout has been ' . $result);
        $this->getHelper()->getCheckoutSession()->addError($errorMessage);

        $additionalErrorMessage = $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_ERROR_PATH_PREFIX . $result));
        if($additionalErrorMessage) {
            $this->getHelper()->getCheckoutSession()->addError($additionalErrorMessage);
        }

        $this->getLogger()->debug($errorMessage);

        switch($result) {
            case Zip_Payment_Model_Api_CheckoutResponseResult::DECLINED:
            case Zip_Payment_Model_Api_CheckoutResponseResult::REFERRED: 
                return $errorMessage;
            case Zip_Payment_Model_Api_CheckoutResponseResult::CANCELLED:
                return null;
            default:
                return 'Something wrong while processing checkout';
        }

    }

}