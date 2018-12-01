<?php

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */
class Zipmoney_ZipmoneyPayment_Model_Checkout
{
    protected $api = null;
    protected $logger;

    /**
     * zipMoney Checkouts Api Class
     *
     * @var string
     */
    protected $_apiClass = '\zipMoney\Api\CheckoutsApi';

    /**
     * @const string
     */
    const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";

    /**
     * Sets the quote and api class. Calls parent constructor
     *
     * @param array $params
     * @throws Mage_Core_Exception
     */
    public function __construct()
    {
        $this->logger = Mage::getSingleton('zipmoneypayment/logger');
    }

    public function getApi()
    {
        if ($this->api === null) {
            $this->api = Mage::getSingleton('zipmoneypayment/api');
        }

        return $this->api;
    }

    /**
     * Starts the checkout process by making checkout api call to the zipMoney API endpoint.
     *
     * @throws Mage_Core_Exception
     * @return \zipMoney\Model\Checkout
     */
    public function start()
    {
        if (!$this->_quote || !$this->_quote->getId()) {
            Mage::throwException(Mage::helper('zipmoneypayment')->__('The quote does not exist.'));
        }

        $checkoutMethod = $this->getCheckoutMethod();
        $isAllowedGuestCheckout = Mage::helper('checkout')->isAllowedGuestCheckout($this->_quote, $this->_quote->getStoreId());
        $isCustomerLoggedIn = $this->getCustomerSession()->isLoggedIn();

        try {
            $this->_quote->reserveOrderId()->save();

            $request = $this->_payload->getCheckoutPayload($this->_quote);

            $this->_logger->debug("Checkout Request:- " . $this->_helper->json_encode($request));

            $checkout = $this->getApi()->checkoutsCreate($request);

            $this->_logger->debug("Checkout Response:- " . $this->_helper->json_encode($checkout));

            if (isset($checkout->error)) {
                Mage::throwException($this->_helper->__('Cannot get redirect URL from zipMoney.'));
            }

            $this->_checkoutId = $checkout->getId();

            $this->_quote->setZipmoneyCid($this->_checkoutId)
                ->save();

            $this->_redirectUrl = $checkout->getUri();
            return $checkout;
        } catch (\zipMoney\ApiException $e) {
            list($apiError, $message, $logMessage) = $this->_handleException($e);
            throw Mage::exception('Mage_Core', $logMessage, 1000);
        }
    }

    /**
     * Returns the zipMoney Redirect Url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * Returns the zipMoney Checkout Id
     *
     * @return string
     */
    public function getCheckoutId()
    {
        return $this->_checkoutId;
    }

}
