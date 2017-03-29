<?php
use \zipMoney\ApiException;

class Zipmoney_ZipmoneyPayment_Model_Observer extends Mage_Core_Model_Abstract
{

	public function __construct()
	{
		$this->_logger = Mage::getSingleton("zipmoneypayment/logger");
		$this->_helper = Mage::helper('zipmoneypayment');
	}

	/**
	 * Include our composer auto loader for the ElasticSearch modules
	 *
	 * @param Varien_Event_Observer $event
	 */
	public function controllerFrontInitBefore(Varien_Event_Observer $event)
	{
		self::init();
	}

	/**
	 * Add in auto loader for Elasticsearch components
	 */
	static function init()
	{

		// Add our vendor folder to our include path
		set_include_path(get_include_path() . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor');
		// Include the autoloader for composer
		require_once(Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor' . DS . 'autoload.php');
	}

  /**
   * check if order was created by zipMoney
   *
   * @param Mage_Sales_Model_Order $oOrder
   * @return bool
   */
  protected function _isZipMoneyOrder(Mage_Sales_Model_Order $order)
  {
    if (!$order || !$order->getId()) {
      $this->_logger->debug("zipMoney Order 1 ");
      return false;
    }
    // check if the order was created by zipMoney
    $payment = $order->getPayment();

    if ($payment && $payment->getId()) {
      if($payment->getMethod()=="zipmoneypayment") {
        return true;
      }
    }

    return false;
  }

	protected function isAvoidInvoicing(Mage_Sales_Model_Order $order)
	{
    if (!$order || !$order->getId()) {
      return false;
    }

    $originalStatus = $order->getOrigData('status');
    $status = $order->getStatus();
    $state = $order->getState();

    // Check if the order was created by zipMoney
    if (!$this->_isZipMoneyOrder($order)) {
      return false;
    }

    /**
     * do not create invoice if any of the follow is true
     *  1) order status from '' to 'zip_pending', and order new state is 'new' or 'processing'
     *  2) order status from 'zip_pending' to 'zip_authorised', and order new state is 'new' or 'processing'
     */
    // if (Mage_Sales_Model_Order::STATE_NEW == $state || Mage_Sales_Model_Order::STATE_PROCESSING == $state) {
    //   if (!$originalStatus  && Zipmoney_ZipmoneyPayment_Model_Config::STATUS_MAGENTO_NEW == $status) {
    //     return true;
    //   }
    //   if (Zipmoney_ZipmoneyPayment_Model_Config::STATUS_MAGENTO_NEW == $originalStatus  && Zipmoney_ZipmoneyPayment_Model_Config::STATUS_MAGENTO_AUTHORIZED == $status) {
    //     return true;
    //   }
    // }

    return false;
	}


  /**
   * Set order invoice_action_flag to false to avoid 3rd party module creating invoice automatically
   *
   * @param Varien_Event_Observer $observer
   */
  public function setInvoiceActionFlag(Varien_Event_Observer $observer)
  {
    /** @var Mage_Sales_Model_Order $oOrder */
    $event = $observer->getEvent();
    $order = $event->getOrder();

    if (!$this->isAvoidInvoicing($order)) {
      return;
    }

    if ($order->getActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_INVOICE) !== false) {
      $originalStatus = $order->getOrigData('status');
      $originalState = $order->getOrigData('state');
      $status = $order->getStatus();
      $state = $order->getState();

      $this->_logger->debug($this->_helper->__('Original state: %s; new state: %s', $originalState, $state));
      $this->_logger->debug($this->_helper->__('Original status: %s; new status: %s', $originalStatus, $status));
      $this->_logger->debug($this->_helper->__('Set order invoice_action_flag to false.'));

      $order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_INVOICE, false);
    }
  }


  /**
   * Notify zipMoney when an order is cancelled
   *
   * @param Varien_Event_Observer $observer
   */
  public function cancelOrder(Varien_Event_Observer $observer)
  {
    /** @var Mage_Sales_Model_Order $order */
    $event = $observer->getEvent();
    $order = $event->getOrder();

    // set scope
    if ($order) {
      Mage::getSingleton('zipmoneypayment/storeScope')->setStoreId($order->getStoreId());
    }

    if (!$this->_isZipMoneyOrder($order)) {
      $this->_logger->debug($this->_helper->__('Order %s was not created by zipMoney. Will not notify zipMoney to cancel order.', $order->getIncrementId()));
      return;
    }

    $originalState = $order->getOrigData('state');
    $curState = $order->getState();

    if ($curState != Mage_Sales_Model_Order::STATE_CANCELED || $originalState == $curState) {
      return false;
    }

    $this->_logger->debug($this->_helper->__('Calling Order Cancel'));

    try {

      $this->_charge = Mage::getModel("zipmoneypayment/charge",
                              array('api_class' => "\zipMoney\Client\Api\ChargesApi",  'order'=>$order));
		  $this->_charge->cancelCharge();
	  } catch (Mage_Core_Exception $e) {
      $this->_logger->debug($e->getMessage());
    } catch (ApiException $e) {
      $this->_logger->debug("Errors:-".json_encode($e->getResponseBody()));
    } catch (Exception $e) {
      $this->_logger->debug($e->getMessage());
    }

    Mage::throwException($this->_helper->__("Unable to cancel the order in zipMoney."));
	}

  public function chargeOrder($observer)
  {
    /** @var Mage_Sales_Model_Order $order */
    $event = $observer->getEvent();
    $order = $event->getOrder();
    $quote = $event->getQuote();

    if($order->getPayment()->getMethod()!="zipmoneypayment")
    {
      return;
    }

    $this->_logger->debug($this->_helper->__("Charge Order"));

    try {

      // Check if the quote exists
      if(!$quote->getId()){
        Mage::throwException($this->_helper->__("The quote doesnot exist."));
      }  
      if(!$order->getId()){
        Mage::throwException($this->_helper->__("The order doesnot exist."));
      }
      // Check if the zipMoney Checkout Id Exists
      if(!$quote->getZipmoneyCid()){
        Mage::throwException($this->_helper->__("The zipMoney Checkout Id doesnot exist."));
      }
      // Check if the Order Has been charged
      if($order->getPayment()->getZipmoneyChargeId()){
        Mage::throwException($this->_helper->__("The order has already been charged."));
      }

      // Initialise the charge
      $this->_charge = Mage::getSingleton('zipmoneypayment/charge');
      // Set quote to the chekout model
      $this->_charge->setOrder($order)
                    ->charge();

    } catch (Mage_Core_Exception $e) {

        $this->_logger->debug($e->getMessage());
        Mage::getSingleton('checkout/session')->addError($message);
        throw new Mage_Payment_Model_Info_Exception($message);

    } catch (Exception $e) {

        $this->_logger->debug($e->getMessage()); 
        $message = $this->_helper("Could not process the payment");
        Mage::getSingleton('checkout/session')->addError($message);
        throw new Mage_Payment_Model_Info_Exception($message); 
    }

  }
}