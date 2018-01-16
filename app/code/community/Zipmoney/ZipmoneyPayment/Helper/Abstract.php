<?php
abstract class Zipmoney_ZipmoneyPayment_Helper_Abstract extends Mage_Core_Helper_Abstract {

  /**
   * @var Zipmoney_ZipmoneyPayment_Model_Logger
   */
  protected $_logger;
  
  /**
   * @var Zipmoney_ZipmoneyPayment_Model_Config
   */
  protected $_config;
  
  /**
   * Set quote and config instances
   */
  public function __construct()
  {   
    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');
    $this->_config = Mage::getSingleton('zipmoneypayment/config');
  }


}

