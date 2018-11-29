<?php

class Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Notice extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'zip/payment/system/config/fieldset/notice.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementOriginalData = $element->getOriginalData();

        if (isset($elementOriginalData['api_url'])) {
            $data = $this->getDataFromAPI($elementOriginalData['api_url']);
            $this->setData($data);
        }
        
        return $this->toHtml();
    }

    /**
     * get data from API
     */
    protected function getDataFromAPI($url) {

        $client = new Zend_Http_Client();

        try {
            $client->setMethod(Zend_Http_Client::GET);
            $client->setUri($url);
            $response = $client->request();
        }
        catch(Exception $e) {

        }
    
        return json_decode($response->getBody(), true);

    }
}
