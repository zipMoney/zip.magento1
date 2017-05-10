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
use \zipMoney\Model\ShopperStatistics;
use \zipMoney\Model\Metadata;
use \zipMoney\Model\CheckoutConfiguration;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */


class Zipmoney_ZipmoneyPayment_Helper_Payload extends Zipmoney_ZipmoneyPayment_Helper_Abstract
{
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_customerSession;

  /**
   * @var Mage_Sales_Model_Quote
   */
  protected $_quote;

  /**
   * @var Mage_Sales_Model_Order
   */
  protected $_order;
  
  /**
   * Sets the quote to the payload object
   * 
   * @param Mage_Sales_Model_Quote $quote
   * @return Zipmoney_ZipmoneyPayment_Helper_Payload
   */
  public function setQuote($quote)
  {
    if($quote){
      $this->_quote = $quote;
    }
    return $this;
  }

  /**
   * Returns the quote from the payload object
   * 
   * @return Mage_Sales_Model_Quote
   */
  public function getQuote()
  {
    if($this->_quote){      
      $this->_order = null;
      return $this->_quote;
    }
    return $this->_quote;
  }

  /**
   * Sets the order to the payload object
   * 
   * @param Mage_Sales_Model_Order $quote
   * @return Zipmoney_ZipmoneyPayment_Helper_Payload
   */
  public function setOrder($order)
  { 
    if($order){
      $this->_quote = null;
      $this->_order = $order;
    }
    return $this;
  }

  /**
   * Returns the order from the payload object
   * 
   * @return Mage_Sales_Model_Order
   */
  public function getOrder()
  {
    if($this->_order){
      return $this->_order;
    } 
    return null;
  }

  /**
   * Returns the prepared checkout request model
   * 
   * @param Mage_Sales_Model_Quote $quote
   * @return \zipMoney\Model\CreateCheckoutRequest
   */
  public function getCheckoutPayload($quote)
  {
    $checkoutReq = new CheckoutRequest();

    $this->setQuote($quote);

    return $checkoutReq->setType("standard")
                ->setShopper($this->getShopper())
                ->setOrder($this->getOrderDetails(new CheckoutOrder))
                ->setMetadata($this->getMetadata())
                ->setConfig($this->getCheckoutConfiguration());
  }

  /**
   * Returns the prepared charge request model
   * 
   * @param Mage_Sales_Model_Order $order
   * @return \zipMoney\Model\CreateChargeRequest
   */
  public function getChargePayload($order)
  {
    $chargeReq = new ChargeRequest();

    $this->setOrder($order);

    $order = $this->getOrder();

    $grand_total = $order->getGrandTotal() ? $order->getGrandTotal() : 0;
    $currency = $order->getOrderCurrencyCode() ? $order->getOrderCurrencyCode() : null;

    return $chargeReq->setAmount((float)$grand_total)
              ->setCurrency($currency)
              ->setOrder($this->getOrderDetails(new ChargeOrder))
              ->setMetadata($this->getMetadata())
              ->setCapture($this->_config->isCharge())
              ->setAuthority($this->getAuthority());
  }

  /**
   * Returns the prepared refund request model
   * 
   * @param Mage_Sales_Model_Order $order
   * @param float $amount
   * @param string $reason
   * @return \zipMoney\Model\CreateRefundRequest
   */
  public function getRefundPayload($order, $amount, $reason)
  {
    $chargeReq = new RefundRequest();

    $this->setOrder($order);

    $currency = $order->getOrderCurrencyCode() ? $order->getOrderCurrencyCode() : null;

    return $chargeReq->setAmount((float)$amount)
              ->setReason($reason)
              ->setChargeId($order->getPayment()->getZipmoneyChargeId())
              ->setMetadata($this->getMetadata());
  }

  /**
   * Returns the prepared capture request model
   * 
   * @param Mage_Sales_Model_Order $order
   * @param float $amount
   * @return \zipMoney\Model\CaptureChargeRequest
   */
  public function getCapturePayload($order, $amount)
  {
    $captureChargeReq = new CaptureChargeRequest();

    $this->setOrder($order);

    $order = $this->getOrder();

    $captureChargeReq->setAmount((float)$amount);

    return $captureChargeReq;
  }

  /**
   * Returns the prepared capture request model
   * 
   * @param Mage_Sales_Model_Order $order
   * @param float $amount
   * @return \zipMoney\Model\CaptureChargeRequest
   */
  public function getCaptureCancelPayload($order, $amount)
  {
    $captureChargeReq = new CaptureChargeRequest();

    $this->setOrder($order);

    $order = $this->getOrder();

    return $captureChargeReq;
  }

  /**
   * Returns the prepared shopper model
   * 
   * @return \zipMoney\Model\Shopper
   */
  public function getShopper()
  {
    $customer = null;
    $shopper = new Shopper;
    if($quote = $this->getQuote()){
      $checkoutMethod = $quote->getCheckoutMethod();

      if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER || 
          $checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) {
        $shopper = $this->getOrderOrQuoteCustomer(new Shopper, $quote);// get shopper data from quote
      } else {
        $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId()); // load customer from database 
      }
      $billing_address = $quote->getBillingAddress();
    } else if($order = $this->getOrder()){
      if ($order->getCustomerIsGuest()) {
        $shopper = $this->getOrderOrQuoteCustomer(new Shopper, $order);// get shopper data from order
      } else {
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId()); // load customer from database 
      }  
      $billing_address = $order->getBillingAddress();
    } else {
      return null;
    }

    if(isset($customer) && $customer->getId()) {
      $shopper = $this->getCustomer(new Shopper, $customer);
    }

    if($billing_address){
      if($address = $this->_getAddress($billing_address)){
        $shopper->setBillingAddress($address);
      }
    }
    return $shopper;
  }

  /**
   * Returns the prepared order shipping model
   * 
   * @return \zipMoney\Model\OrderShipping
   */
  public function getShippingDetails()
  {    
    $shipping = new OrderShipping;

    if($this->getQuote()){
      $shipping_address = $this->getQuote()->getShippingAddress();
    } else if($this->getOrder()) {
      $shipping_address = $this->getOrder()->getShippingAddress();

      if($shipping_address){
        if( $shipping_method = $shipping_address->getShippingMethod()){    
          $tracking = new OrderShippingTracking;
          $tracking->setNumber($this->getTrackingNumbers())
                   ->setCarrier($shipping_method);

          $shipping->setTracking($tracking);         
        }
      }
    }
    
    if($shipping_address){      
      if($address = $this->_getAddress($shipping_address)){     
        $shipping->setPickup(false)
                 ->setAddress($address);
      }  
    } else {        
      $shipping->setPickup(true);
    }

    return $shipping;
  }

  /**
   * Returns the prepared order details model
   * 
   * @param CheckoutOrder|ChargeOrder $reqOrder
   * @return \zipMoney\Model\CheckoutOrder |  \zipMoney\Model\CheckoutOrder
   */
  public function getOrderDetails($reqOrder)
  {
    $reference = 0;
    $cart_reference = 0;

    if($quote = $this->getQuote()){
      $shipping_address = $quote->getShippingAddress();
      $reference = $quote->getReservedOrderId() ? $quote->getReservedOrderId() : '0';
      $cart_reference = $quote->getId();
      $shipping_amount = $shipping_address ? $shipping_address->getShippingInclTax():0.00;
      $discount_amount = $shipping_address ? $shipping_address->getDiscountAmount():0.00;
      $tax_amount = $shipping_address ? $shipping_address->getTaxAmount():0.00;
      $grand_total = $quote->getGrandTotal() ? $quote->getGrandTotal() : 0.00;
      $currency = $quote->getQuoteCurrencyCode() ? $quote->getQuoteCurrencyCode() : null;
    } else if($order = $this->getOrder()){
      $reference = $order->getIncrementId() ? $order->getIncrementId() : '0';
      $shipping_amount = $order->getShippingAmount() ? $order->getShippingAmount()  + $order->getShippingTaxAmount() : 0;
      $discount_amount = $order->getDiscountAmount() ? $order->getDiscountAmount() : 0;
      $tax_amount = $order->getTaxAmount() ? $order->getTaxAmount() : 0;
     }
  
    $orderItems = $this->getOrderItems();
    

    // Discount Item
    if($discount_amount <  0){
      $discountItem = new OrderItem;
      $discountItem->setName("Discount");
      $discountItem->setAmount((float)$discount_amount);      
      $discountItem->setQuantity(1);      
      $discountItem->setType("discount");
      $orderItems[] = $discountItem;
    }

    // Shipping Item
    if($shipping_amount > 0){
      $shippingItem = new OrderItem;      
      $shippingItem->setName("Shipping");
      $shippingItem->setAmount((float)$shipping_amount);
      $shippingItem->setType("shipping");      
      $shippingItem->setQuantity(1);      
      $orderItems[] = $shippingItem;
    }

    if(isset($grand_total) && $quote)
      $reqOrder->setAmount($grand_total);
    
    if(isset($currency) && $quote)
       $reqOrder->setCurrency($currency);

    if($cart_reference)
     $reqOrder->setCartReference((string)$cart_reference);

    $reqOrder->setReference($reference)
            ->setShipping($this->getShippingDetails())
            ->setItems($orderItems);
    return $reqOrder;      
  }

  /**
   * Returns the prepared order item model
   * 
   * @return \zipMoney\Model\OrderItem[]
   */
  public function getOrderItems()
  {
    if($quote = $this->getQuote()){
      $items = $quote->getAllItems();
      $storeId   = $quote->getStoreId();
    } else if($order = $this->getOrder()){
      $items = $order->getAllItems();      
      $storeId = $order->getStoreId();
    }

    $itemsArray = array();

    /** @var Mage_Sales_Model_Order_Item $oItem */
    foreach($items as $item) {
      if($item->getParentItemId()) {
        continue;   // Only sends parent items to zipMoney
      }
      $orderItem = new OrderItem;
      
      if ($item->getDescription()) {
        $description = $item->getDescription();
      } else {
        $description = $this->_getProductShortDescription($item, $storeId);
      }
      if($quote){
        $qty = $item->getQty();
      } else if($order){
        $qty = $item->getQtyOrdered();
      }
    
      $orderItem->setName($item->getName())
                ->setAmount($item->getPriceInclTax() ? (float)$item->getPriceInclTax() : 0.00)
                ->setReference((string)$item->getId())
                ->setDescription($description)
                ->setQuantity(round($qty))
                ->setType("sku")
                ->setImageUri($this->_getProductImage($item))
                ->setItemUri($item->getProduct()->getProductUrl())
                ->setProductCode($item->getSku());  
      $itemsArray[] = $orderItem;
    }

   return $itemsArray;       
  }

  /**
   * Returns the prepared metadata model
   * 
   * @return \zipMoney\Model\Metadata
   */
  public function getMetadata()
  { 
    $metadata = new Metadata;
    return $metadata;
  }

  /**
   * Returns the prepared authority model
   * 
   * @return \zipMoney\Model\Authority
   */
  public function getAuthority()
  { 
    $quoteId = $this->getOrder()->getQuoteId();
    $quote = Mage::getModel('sales/quote')->load($quoteId);
    $checkout_id = $quote->getZipmoneyCid();

    $authority = new Authority;
    $authority->setType('checkout_id')
              ->setValue($checkout_id);
  
    return $authority;
  }

  /**
   * Returns the prepared checkout configuration model
   * 
   * @return \zipMoney\Model\CheckoutConfiguration
   */
  public function getCheckoutConfiguration()
  {
    $checkout_config = new CheckoutConfiguration();
    $redirect_url = Mage::helper("zipmoneypayment")->getUrl('zipmoneypayment/complete');

    $checkout_config->setRedirectUri($redirect_url);

   return $checkout_config;

  }

  /**
   * Sets shopper from given quote | order
   *
   * @param \zipMoney\Model\Shopper $shopper
   * @param Mage_Sales_Model_Order | Mage_Sales_Model_Quote $order_or_quote
   * @return \zipMoney\Model\Shopper
   */
  public function getOrderOrQuoteCustomer($shopper, $order_or_quote)
  {
    if(!$order_or_quote) {
      return null;
    }

    $shopper->setFirstName($order_or_quote->getCustomerFirstname())
            ->setLastName($order_or_quote->getCustomerLastname())
            ->setEmail($order_or_quote->getCustomerEmail());
    
    if ($order_or_quote->getCustomerGender()) {      
      $shopper->setGender($this->_getGenderText($order_or_quote->getCustomerGender()));
    }

    if ($order_or_quote->getCustomerDob()) {      
      $shopper->setBirthDate($order_or_quote->getCustomerDob());
    }

    if ($order_or_quote->getCustomerPrefix()) {
      $shopper->setTitle($order_or_quote->getCustomerPrefix());
    }
    
    if ($phone = $order_or_quote->getShippingAddress()->getTelephone()) {      
      $shopper->setPhone($phone);
    }
               
    return $shopper;
  }


  /**
   * Sets shopper from given customer 
   *
   * @param \zipMoney\Model\Shopper $shopper
   * @param  Mage_Customer_Model_Customer $customer
   * @return \zipMoney\Model\Shopper
   */
  public function getCustomer($shopper, $customer)
  {
    if(!$customer || !$customer->getId()) {
      return null;
    }

    $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer);
    $customerData = Array();


    if(Mage::helper('customer')->isLoggedIn() || $customer->getId()) {
        // get customer merchant history
      $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => array($customer->getId())))
            ->addFieldToFilter('state', array(
                array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE),
                array('eq' => Mage_Sales_Model_Order::STATE_CLOSED)
            ));
      
      $lifetimeSalesAmount           = 0;        // total amount of complete orders
      $maximumSaleValue              = 0;        // Maximum single order amount among complete orders
      $lifetimeSalesRefundedAmount   = 0;        // Total refunded amount (of closed orders)
      $averageSaleValue              = 0;        // Average order amount
      $orderNum                      = 0;        // Total number of orders
      $declinedBefore                = false;    // the number of declined payments
      $chargeBackBefore              = false;    // any payments that have been charged back by their bank or card provider.
                                                //  A charge back is when a customer has said they did not make the payment, and the bank forces a refund of the amount
      foreach ($orderCollection AS $order) {
        if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
            $orderNum++;
            $lifetimeSalesAmount += $order->getGrandTotal();
            if ($order->getGrandTotal() > $maximumSaleValue) {
                $maximumSaleValue = $order->getGrandTotal();
            }
        } else if ($order->getState() == Mage_Sales_Model_Order::STATE_CLOSED) {
            $lifetimeSalesRefundedAmount += $order->getGrandTotal();
        }
      }

      if ($orderNum > 0) {
        $averageSaleValue = (float)round($lifetimeSalesAmount / $orderNum, 2);
      }

      if ($customer->getGender()) {
        $shopper->setGender($this->_getGenderText($customer->getGender()));
      }

      if ($customer->getDob()) {
        $shopper->setBirthDate($customer->getDob());
      }

      foreach ($customer->getAddresses() as $address) {
        if ($address->getTelephone()) {
          $shopper->setPhone($address->getTelephone());
          break;
        }
      }

      if ($customer->getPrefix()) {
        $shopper->setTitle($customer->getPrefix());
      }

      $shopper->setEmail($customer->getEmail());
      $shopper->setFirstName($customer->getFirstname());
      $shopper->setLastName($customer->getLastname());
      
      $statistics = new ShopperStatistics;

      $statistics->setAccountCreated($customer->getCreatedAt())
               ->setSalesTotalCount((int)$orderNum)
               ->setSalesTotalAmount((float)$lifetimeSalesAmount)
               ->setSalesAvgAmount((float)$averageSaleValue)
               ->setSalesMaxAmount((float)$maximumSaleValue)
               ->setRefundsTotalAmount((float)$lifetimeSalesRefundedAmount)
               ->setPreviousChargeback($chargeBackBefore)
               ->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());

      if ($logCustomer->getLoginAtTimestamp()) {
        $statistics->setLastLogin(date('Y-m-d H:i:s', $logCustomer->getLoginAtTimestamp()));
      }      

      $shopper->setStatistics($statistics);
    }

    return $shopper;
  }

  /**
   * Sets address for the request
   *
   * @param $address
   * @return \zipMoney\Model\Address
   */
  protected function _getAddress($address)
  {
    if(!$address) {
      return null;
    }

    if(!$address->getStreet1()
        || !$address->getCity()
        || !$address->getCountryId()
        || !$address->getPostcode()
    ) {
      return null;
    }

    $reqAddress = new Address;

    if($address && $address->getId()) {
      $reqAddress->setFirstName($address->getFirstname());
      $reqAddress->setLastName($address->getLastname());
      $reqAddress->setLine1($address->getStreet1());
      $reqAddress->setLine2($address->getStreet2());
      $reqAddress->setCountry($address->getCountryId());
      $reqAddress->setPostalCode($address->getPostcode());
      $reqAddress->setCity($address->getCity());

      // $this->_logger->debug($address->getRegion());

      /**
       * If region_id is null, the state is saved in region directly, so the state can be got from region.
       * If region_id is a valid id, the state should be got by getRegionCode.
       */
      if ($address->getRegionId()) {
        $reqAddress->setState($address->getRegionCode());
      } else {              
        $reqAddress->setState($address->getRegion());
      }
      return $reqAddress;
    }
    return null;
  }

  /**
   * Retrieves the gender text
   *
   * @param  string $gender
   * @return  string
   */
  protected function _getGenderText($gender)
  {
      $genderText = Mage::getModel('customer/customer')->getResource()
          ->getAttribute('gender')
          ->getSource()
          ->getOptionText($gender);
      return $genderText;
  }

  /**
   * Retrieves the child product
   *
   * @param  Mage_Sales_Model_Quote_Item | Mage_Sales_Model_Order_Item $item
   * @return  Mage_Catalog_Model_Product
   */
  public function getChildProduct($item)
  {
    if ($option = $item->getOptionByCode('simple_product')) {
        return $option->getProduct();
    }
    return $item->getProduct();
  }

  /**
   * Retrieves the product image
   *
   * @param  Mage_Sales_Model_Quote_Item | Mage_Sales_Model_Order_Item $item
   * @return  string
   */
  protected function _getProductImage($item)
  {
    $imageUrl = '';
    try {
      $product = $this->getChildProduct($item);
      if (!$product || !$product->getData('thumbnail')
          || ($product->getData('thumbnail') == 'no_selection')
          || (Mage::getStoreConfig("checkout/cart/configurable_product_image") == 'parent')) {
          $product =  $item->getProduct();
      }           
      $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'thumbnail');
    } catch (Exception $e) {
      $this->_logger->warn($this->__('An error occurred during getting item image for product ' . $product->getId() . '.'));
      $this->_logger->error($e->getMessage());
      $this->_logger->debug($e->getTraceAsString());
    }
    return $imageUrl;
  }

  /**
   * Retrieves the product short description
   *
   * @param  Mage_Sales_Model_Quote_Item | Mage_Sales_Model_Order_Item $item 
   * @param  int $storeId 
   * @return  string
   */
  private function _getProductShortDescription($item, $storeId)
  {
    $product = $this->getChildProduct($item);
    
    if (!$product) {
      $product = $item->getProduct();
        
      $description = $product->getShortDescription();

      if (!$description) {
        $description = $product->getResource()->getAttributeRawValue($product->getId(), 'short_description', $storeId);
      } 
      return $description;
    }    
    $description = $product->getShortDescription();
    if (!$description) {
      $description = $product->getResource()->getAttributeRawValue($product->getId(), 'short_description', $storeId);
    }  
    return $description;
  }

}