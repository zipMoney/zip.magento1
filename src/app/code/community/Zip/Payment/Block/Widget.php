<?php

/**
 * Block model for widgets
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Widget extends Mage_Core_Block_Template
{

    const CONFIG_WIDGET_PATH_PREFIX = 'payment/zip_payment/widgets/';
    const CONFIG_WIDGETS_ENABLED_PATH = 'payment/zip_payment/widgets/enabled';
    const CONFIG_WIDGETS_DEBUG_PATH = 'payment/zip_payment/widgets/debug';
    const CONFIG_WIDGETS_LIB_SCRIPT_PATH = 'payment/zip_payment/widgets/js_lib';

    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';
    const CONFIG_ENVIRONMENT_PATH = 'payment/zip_payment/environment';
    const CONFIG_HOME_PAGE_PATH = 'web/default/cms_home_page';

    protected $_supportedWidgetTypes = array('widget', 'banner', 'tagline');

    /**
     * Config instance
     *
     * @var Zip_Payment_Model_Config
     */
    protected $_config = null;

    /**
     * Config instance getter
     *
     * @return Zip_Payment_Model_Config
     */
    public function getConfig()
    {
        if ($this->_config == null) {
            $this->_config = Mage::helper('zip_payment')->getConfig();
        }

        return $this->_config;
    }

    /**
     * get merchant id from public key
     */
    public function getMerchantId()
    {
        return $this->getConfig()->getValue(self::CONFIG_PUBLIC_KEY_PATH);
    }

    /**
     * get current environment
     */
    public function getEnvironment()
    {
        return $this->getConfig()->getValue(self::CONFIG_ENVIRONMENT_PATH);
    }

    /**
     * get url of widget js library
     */
    public function getLibScript()
    {
        return $this->getConfig()->getValue(self::CONFIG_WIDGETS_LIB_SCRIPT_PATH);
    }

    /**
     * is debug mode enabled
     */
    protected function isDebugModeEnabled()
    {
        return $this->getConfig()->getFlag(self::CONFIG_WIDGETS_DEBUG_PATH);
    }

    /**
     * check is one widget type is enabled / active
     */
    protected function isActive()
    {
        if (Mage::helper('zip_payment')->isActive() && $this->getConfig()->getFlag(self::CONFIG_WIDGETS_ENABLED_PATH)) {
            $pageType = $this->getWidgetPageType();

            if ($pageType === null) {
                return false;
            }

            if ($pageType == 'checkout' || $pageType == 'landing') {
                return true;
            }

            foreach ($this->_supportedWidgetTypes as $widgetType) {
                $enabled = $this->getConfig()
                    ->getValue(self::CONFIG_WIDGET_PATH_PREFIX . $pageType . '_page/' . $widgetType . '/enabled');

                /**
                 * Make sure there one widget type is enable for current page type
                 */
                if ($enabled !== null && $enabled) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * get element selectors for current widgets
     */
    protected function getElementSelectors()
    {
        $selectors = array();
        $helper = Mage::helper('zip_payment');

        foreach ($this->_supportedWidgetTypes as $widgetType) {
            $pageType = $this->getWidgetPageType();
            $path = self::CONFIG_WIDGET_PATH_PREFIX . $pageType . '_page/' . $widgetType;
            $enabled = $helper->getConfig()->getValue($path . '/enabled');

            if ($enabled !== null && $enabled) {
                $widgetType = $widgetType == 'widget' ? $pageType . '_' . $widgetType : $widgetType;
                $selectors[$widgetType] = $helper->getConfig()->getValue($path . '/selector');
            }
        }

        return $selectors;
    }

    /**
     * Returns the current page type.
     *
     * @return string
     */
    protected function getWidgetPageType()
    {
        $helper = Mage::helper('zip_payment');
        $pageIdentifier = $helper->getPageIdentifier();

        if ($helper->isCheckoutPage()) {
            return 'checkout';
        }

        switch ($pageIdentifier) {
        case 'cms_index_index':
            return 'home';
        case 'catalog_product_view':
            return 'product';
        case 'catalog_category_view':
            return 'category';
        case 'checkout_cart_index':
            return 'cart';
        case 'cms_page_view':
            return 'landing';
        }

        return null;
    }


}
