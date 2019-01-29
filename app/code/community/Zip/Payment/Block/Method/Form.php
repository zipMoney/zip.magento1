<?php

/**
 * Block model of checkout method form
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Method_Form extends Mage_Payment_Block_Form
{
    protected $template = 'zip/payment/method/form/default.phtml';
    protected $labelTemplate = 'zip/payment/method/form/label.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate($this->template);
        $this->setMethodLabelAfterHtml($this->getMethodLabelHtml());
        $this->setMethodTitle("");     
    }

    protected function getMethodLabelHtml() {

        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->labelTemplate);
        $config = Mage::helper('zip_payment')->getConfig();
        
        $block->setData(array(
            'logo' => $config->getLogo(),
            'title' => $config->getTitle(),
            'method_code' => $config->getMethodCode()
        ));

        return $block->toHtml();
    }
    

}
