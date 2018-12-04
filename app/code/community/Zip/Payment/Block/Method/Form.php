<?php


class Zip_Payment_Block_Method_Form extends Mage_Payment_Block_Form
{

    protected $formTemplate = 'zip/payment/method/form.phtml';
    protected $methodLabelTemplate = 'zip/payment/method/label.phtml';


    /**
     * Config model instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;

    const CONFIG_LOGO_PATH = 'payment/zip_payment/logo';
    const CONFIG_TITLE_PATH = 'payment/zip_payment/title';

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate($this->formTemplate);
        $this->setMethodLabelAfterHtml($this->getMethodLabelHtml());
        $this->setMethodTitle("");     
    }

    protected function getMethodLabelHtml() {
        $config = Mage::getSingleton('zip_payment/config');

        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->methodLabelTemplate);
        $block->setData(array(
            'logo' => $config->getValue(self::CONFIG_LOGO_PATH),
            'title' => $config->getValue(self::CONFIG_TITLE_PATH),
            'method_code' => $config->getMethodCode()
        ));

        return $block->toHtml();
    }
    

}
