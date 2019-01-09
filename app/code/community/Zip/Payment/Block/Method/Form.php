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

    /**
     * Config model instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate($this->template);
        $this->setMethodLabelAfterHtml($this->getMethodLabelHtml());
        $this->setMethodTitle("");     
    }

    /**
     * Config instance getter
     * @return Zip_Payment_Model_Config
     */
    public function getConfig()
    {
        if ($this->config == null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }
        return $this->config;
    }

    protected function getMethodLabelHtml() {

        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->labelTemplate);
        $block->setData(array(
            'logo' => $this->getConfig()->getLogo(),
            'title' => $this->getConfig()->getTitle(),
            'method_code' => $this->getConfig()->getMethodCode()
        ));

        return $block->toHtml();
    }
    

}
