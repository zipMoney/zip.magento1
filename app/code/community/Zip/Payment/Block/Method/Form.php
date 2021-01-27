<?php

/**
 * Block model of checkout method form
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Method_Form extends Mage_Payment_Block_Form
{
    protected $_template = 'zip/payment/method/form/default.phtml';
    protected $_labelTemplate = 'zip/payment/method/form/label.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate($this->_template);
        $this->setMethodLabelAfterHtml($this->getMethodLabelHtml());
        $this->setMethodTitle("");
    }

    protected function getMethodLabelHtml()
    {
        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->_labelTemplate);
        $config = Mage::helper('zip_payment')->getConfig();

        $block->setData(
            array(
                'logo' => $config->getLogo(),
                'title' => $config->getTitle(),
                'method_code' => $config->getMethodCode()
            )
        );

        return $block->toHtml();
    }

    public function getQuoteCurrencyCode()
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote = $checkoutSession->getQuote();
        if ($quote) {
            return $quote->getQuoteCurrencyCode();
        } else {
            return null;
        }
    }

    public function getCartTotal()
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        $quote = $checkoutSession->getQuote();
        if ($quote) {
            return $quote->getGrandTotal();
        } else {
            return null;
        }
    }

    public function getRepaymentData($currency, $total)
    {
        $repayment = array();
        if (is_string($currency) 
            && strtoupper($currency) !== \Zip\Model\CurrencyUtil::CURRENCY_AUD
            && is_numeric($total) 
            && $total > 0
        ) {
            $payInTimes = 4;
            $repayment['Grand Total'] = round($total, 2);
            $now = strtotime("now");
            $div = round($total, 2)/4;
            $payment = round($div, 2);
            $diff = ($div - $payment) * $payInTimes;
            $todayLabel = date("d M", $now);
            for ( $i = 1; $i <= $payInTimes; $i++) {
                if ($i == 1) {
                    $repayment['Today\'s Payment ('.$todayLabel.')'] = round($payment, 2);
                    continue;
                }
                $d = ($i - 1) * 14;
                if ($i == $payInTimes) {
                    $repayment["Payment $i (" .date("d M", strtotime("+$d day", $now)) . ')'] = round($payment + $diff, 2);
                    continue;
                }
                $repayment["Payment $i (" .date("d M", strtotime("+$d day", $now)) . ')'] = round($payment, 2);
            }
        }
        return $repayment;
    }
}
