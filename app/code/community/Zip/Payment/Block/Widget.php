<?php

class Zip_Payment_Block_Widget extends Mage_Core_Block_Template
{

    const CONFIG_WIDGET_PATH_PREFIX = 'payment/zip_payment/widgets/';
    const CONFIG_WIDGETS_ENABLED_PATH = 'payment/zip_payment/widgets/enabled';
    const CONFIG_WIDGETS_LIB_SCRIPT_PATH = 'payment/zip_payment/widgets/js_lib';
    
    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';
    const CONFIG_ENVIRONMENT_PATH = 'payment/zip_payment/environment';
    const CONFIG_HOME_PAGE_PATH = 'web/default/cms_home_page';
    
    const ROOT_WIDGET_TYPES = array('head', 'root', 'popup');
    const SUPPORTED_WIDGET_TYPES = array('widget', 'banner', 'tagline');

    /**
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;

    protected function _construct()
    {
        parent::_construct();
        $this->config = Mage::getSingleton('zip_payment/config');
    }

    protected function isActive($widgetType) 
    {

        if(Mage::helper('zip_payment')->isActive() && $this->config->getFlag(self::CONFIG_WIDGETS_ENABLED_PATH)) {

            $pageType = $this->getWidgetPageType();
   
            if(!empty($pageType)) {

                if(in_array($widgetType, self::ROOT_WIDGET_TYPES)) {

                    foreach(self::SUPPORTED_WIDGET_TYPES as $supportedWidgetType) {
                        $path = self::CONFIG_WIDGET_PATH_PREFIX . $pageType . '_page/' . $supportedWidgetType;
                        $enabled = $this->config->getValue($path);
                        
                        /**
                         * Make sure there one widget type is enable for current page type
                         */
                        if(!is_null($enabled) && boolval($enabled)) {
                            return true;
                        }
                    }
    
                    return false;
    
                }
                elseif(in_array($widgetType, self::SUPPORTED_WIDGET_TYPES)) {
    
                    if(!empty($widgetType)) {
                        $path = self::CONFIG_WIDGET_PATH_PREFIX . $pageType . '_page/' . $widgetType;
                        return $this->config->getFlag($path);
                    }
    
                }

            }

        }

        return false;
    }

    public function getMerchantId() {
        return $this->config->getValue(self::CONFIG_PUBLIC_KEY_PATH);
    }

    public function getEnvironment() {
        return $this->config->getValue(self::CONFIG_ENVIRONMENT_PATH);
    }

    public function getLibScript() {
        return $this->config->getValue(self::CONFIG_WIDGETS_LIB_SCRIPT_PATH);
    }

    /**
     * Returns the current page type.
     *
     * @return string
     */
    protected function getWidgetPageType()
    {
        $pageIdentifier = Mage::app()->getFrontController()->getAction()->getFullActionName();

        switch($pageIdentifier){
            case 'cms_index_index': return 'home';
            case 'catalog_product_view': return 'product'; 
            case 'catalog_category_view': return 'category'; 
            case 'checkout_cart_index': return 'cart';
            case 'checkout_onepage_index': return 'checkout';
        }

        return null;
    }

    
}