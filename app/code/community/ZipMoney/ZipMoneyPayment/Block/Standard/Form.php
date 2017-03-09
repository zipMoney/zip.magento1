<?php

class Zipmoney_ZipmoneyPayment_Block_Standard_Form extends Mage_Payment_Block_Form {

    protected $_methodCode = 'zipmoneypayment';
    protected $_config;

    protected function _construct() {
        $message = '';

        $min = Mage::getStoreConfig('payment/zipmoney_checkout/minimum_total');
        $max = Mage::getStoreConfig('payment/zipmoney_checkout/maximum_total');
        
        $showmessage    = Mage::getStoreConfig('payment/zipmoney_checkout/display_message');
        $message_notice = Mage::getStoreConfig('payment/zipmoney_checkout/message');
        
        $total          = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        $product        = Mage::getStoreConfig('payment/zipmoney/product');

        if (Mage::getStoreConfig('payment/zipmoney/displaydetail')) {
            $message_ = Mage::helper('zipmoneypayment')->__(Mage::getStoreConfig('payment/zipmoney_checkout/detailmessage'));
            $message .= '<b>' . $message_ . ' </b><a href="#" id="zipmoney-learn-more" class="zip-hover"  zm-widget="popup"  zm-popup-asset="checkoutdialog">';
            $message .= Mage::helper('zipmoneypayment')->__('Learn more');
            $message .= '</a><script>if(window.$zmJs!=undefined) window.$zmJs._collectWidgetsEl(window.$zmJs);</script>';
        }


        if (($total < $min || $total > $max) && $showmessage == '1') {
            $message.='<span style="color: red; display: block">' .  Mage::helper('zipmoneypayment')->__($message_notice,1000). "</span>";
        }

        if(empty($message)){
            $message =  Mage::helper('zipmoneypayment')->__("You will be redirected to the %s website when you place an order.",$product);
        }

        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('zipmoney/zipmoneypayment/mark.phtml')
             ->setPaymentAcceptanceMarkSrc('http://d3k1w8lx8mqizo.cloudfront.net/logo/25px/zipmoney.png'); 
        // known issue: code above will render only static mark image
        $this->setTemplate('zipmoney/zipmoneypayment/redirect.phtml')
                ->setRedirectMessage($message)
                //->setMethodTitle('zipMon') // Output PayPal mark, omit title
                ->setMethodLabelAfterHtml($mark->toHtml());

        return parent::_construct();
    }

    public function getMethodCode() {
        return $this->_methodCode;
    }

}
