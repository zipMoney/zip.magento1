<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Standard_Form extends Mage_Payment_Block_Form
{
    /**
     * @var string
     */
    protected $_methodCode = 'zipmoneypayment';
    /**
     * @var Zipmoney_ZipmoneyPayment_Model_Config
     */
    protected $_config;
    /**
     * @var Zipmoney_ZipmoneyPayment_Model_Logger
     */
    protected $_logger;

    /**
     * Prepares the payment option mark for checkout page
     *
     */
    protected function _construct()
    {
        $this->_config = Mage::getSingleton("zipmoneypayment/config");
        $this->_logger = Mage::getSingleton("zipmoneypayment/logger");

        $message = '';

        $order_total = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();

        //$detail_message = Mage::helper('zipmoneypayment')->__($this->_config->getDetailMessage());

        $detail_message = "<span zm-widget='inline' zm-asset='checkoutdescription'></span>";
        $message .= $detail_message . ' <a href="#" id="zipmoney-learn-more" class="zip-hover"  zm-widget="popup"  zm-popup-asset="checkoutdialog">';
        $message .= Mage::helper('zipmoneypayment')->__('Learn more');
        $message .= '</a><script>if(window.$zmJs!=undefined) window.$zmJs._collectWidgetsEl(window.$zmJs);</script>';

        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;

        $mark->setTemplate('zipmoney/zipmoneypayment/mark.phtml')->setPaymentAcceptanceMarkSrc($this->_config->getMethodLogo());

        $this->setTemplate('zipmoney/zipmoneypayment/redirect.phtml')->setRedirectMessage($message)->setMethodLabelAfterHtml($mark->toHtml());

        if (!$this->_config->getDisplayMethodTitle()) {
            $this->setMethodTitle("");
        }

        return parent::_construct();
    }

    /**
     * Returns the method code.
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

}
