<?php


class Zip_Payment_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected $formTemplate = 'zip/payment/checkout/form.phtml';
    protected $methodLabelTemplate = 'zip/payment/checkout/label.phtml';

    /**
     * Payment method code
     * @var string
     */
    protected $methodCode = Zip_Payment_Model_Config::METHOD_CODE;

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

        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->methodLabelTemplate);
        $block->setData(array(
            'logo' => Mage::getStoreConfig(self::CONFIG_LOGO_PATH),
            'title' => Mage::getStoreConfig(self::CONFIG_TITLE_PATH),
            'method_code' => $this->getMethodCode()
        ));

        return $block->toHtml();
    }

    /**
     * Payment method code getter
     * @return string
     */
    public function getMethodCode()
    {
        return $this->methodCode;
    }

}
