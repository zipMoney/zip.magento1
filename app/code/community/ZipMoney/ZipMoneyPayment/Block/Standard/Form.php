<?php

class Zipmoney_ZipmoneyPayment_Block_Standard_Form extends Mage_Payment_Block_Form {

	protected $_methodCode = 'zipmoneypayment';
	protected $_config;
	protected $_logger;

	protected function _construct() {
		$this->_config = Mage::getSingleton("zipmoneypayment/config");
		$this->_logger = Mage::getSingleton("zipmoneypayment/logger");

		$message = '';

		$order_total_minimum = $this->_config->getOrderTotalMinimum();
		$order_total_maximum = $this->_config->getOrderTotalMaximum();
		
		$order_total_notice = $this->_config->getOrderTotalOutsideThresholdNotice();

		$order_total = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
		$product = Mage::getStoreConfig('payment/zipmoneypayment/product');

		if ($this->_config->getDisplayDetailMessage()) {
			$detail_message = Mage::helper('zipmoneypayment')->__($this->_config->getDetailMessage());
			
			$message .= '<b>' . $detail_message . ' </b><a href="#" id="zipmoney-learn-more" class="zip-hover"  zm-widget="popup"  zm-popup-asset="checkoutdialog">';
			$message .= Mage::helper('zipmoneypayment')->__('Learn more');
			$message .= '</a><script>if(window.$zmJs!=undefined) window.$zmJs._collectWidgetsEl(window.$zmJs);</script>';
		}


		if (($order_total < $order_total_minimum || $order_total > $order_total_maximum) && 
			  $this->_config->getOrderTotalOutsideThresholdAction() == 'display_notice') {
			
			$message.='<span style="color: red; display: block">' .  Mage::helper('zipmoneypayment')->__($order_total_notice,$order_total_maximum). "</span>";
		}

		if(empty($message)){
			$message =  Mage::helper('zipmoneypayment')->__("You will be redirected to the %s website when you place an order.",$product);
		}

		$mark = Mage::getConfig()->getBlockClassName('core/template');
		$mark = new $mark;

		$mark->setTemplate('zipmoney/zipmoneypayment/mark.phtml')
				 ->setPaymentAcceptanceMarkSrc($this->_config->getMethodLogo()); 

		// known issue: code above will render only static mark image
		$this->setTemplate('zipmoney/zipmoneypayment/redirect.phtml')
						->setRedirectMessage($message)
						->setMethodLabelAfterHtml($mark->toHtml());

		if(!$this->_config->getDisplayMethodTitle()){
			$this->setMethodTitle("");
		}

		return parent::_construct();
	}

	public function getMethodCode() {
		return $this->_methodCode;
	}

}
