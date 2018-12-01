<?php

class Zip_Payment_Block_Widget extends Mage_Core_Block_Template
{

    /**
     * @const string
     */
    const CONFIG_WIDGET_PATH_PREFIX = 'payment/zip_payment/widgets/';
    const CONFIG_WIDGETS_ENABLED_PATH = 'payment/zip_payment/widgets/enabled';
    const CONFIG_WIDGETS_LIB_SCRIPT_PATH = 'payment/zip_payment/widgets/js_lib';
    
    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';
    const CONFIG_ENVIRONMENT_PATH = 'payment/zip_payment/environment';
    
    const ROOT_WIDGET_TYPES = array('head', 'root', 'popup');
    const PAGE_WIDGET_TYPES = array('widget', 'banner', 'tagline');

    protected function isActive($widgetType) 
    {

        if(Mage::helper("zip_payment")->isActive() && (bool)Mage::getStoreConfig(self::CONFIG_WIDGETS_ENABLED_PATH)) {

            $pageType = $this->getCurrentPageType();

            if(in_array($widgetType, self::ROOT_WIDGET_TYPES)) {

                foreach(self::PAGE_WIDGET_TYPES as $supportedWidgetType) {
                    $path = self::CONFIG_WIDGET_PATH_PREFIX . $pageType . '_page/' . $supportedWidgetType;
                    $enabled = Mage::getStoreConfig($path);
                    
                    if(!is_null($enabled) && $enabled == 0) {
                        return false;
                    }
                }

                return true;

            }
            elseif(in_array($widgetType, self::PAGE_WIDGET_TYPES)) {

                if(!empty($pageType) && !empty($widgetType)) {
                    $path = self::CONFIG_WIDGET_PATH_PREFIX . $pageType . '_page/' . $widgetType;
                    return Mage::getStoreConfig($path) == 1;
                }

            }
        }

        return false;
    }

    public function getMerchantId() {
        return Mage::getStoreConfig(self::CONFIG_PUBLIC_KEY_PATH);
    }

    public function getEnvironment() {
        return Mage::getStoreConfig(self::CONFIG_ENVIRONMENT_PATH);
    }

    /**
     * Returns the current page type.
     *
     * @return string
     */
    protected function getCurrentPageType()
    {
        $oRequest = Mage::app()->getRequest();
        $vModule = $oRequest->getModuleName();
        if ($vModule == 'cms') {
                $vId = Mage::getSingleton('cms/page')->getIdentifier();
                $iPos = strpos($vId, 'home');
            if ($iPos === 0) {
                    return 'home';
            }
        } else if ($vModule == 'catalog') {
                $vController = $oRequest->getControllerName();
            if ($vController == 'product') {
                    return 'product';
            } else if ($vController == 'category') {
                    return 'category';
            }
        } else if ($vModule == 'checkout') {
                $vController = $oRequest->getControllerName();
            if ($vController == 'cart') {
                    return 'cart';
            }
        }

        return '';
    }

    public function getLibScript() {
        return Mage::getStoreConfig(self::CONFIG_WIDGETS_LIB_SCRIPT_PATH);
    }
}