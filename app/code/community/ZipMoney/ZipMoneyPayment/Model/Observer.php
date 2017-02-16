<?php

class Zipmoney_ZipmoneyPayment_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Include our composer auto loader for the ElasticSearch modules
     *
     * @param Varien_Event_Observer $event
     */
    public function controllerFrontInitBefore(Varien_Event_Observer $event)
    {
        self::init();
    }

    /**
     * Add in auto loader for Elasticsearch components
     */
    static function init()
    {
        // Add our vendor folder to our include path
        set_include_path(get_include_path() . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor');

        // Include the autoloader for composer
        require_once(Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor' . DS . 'autoload.php');
//echo "<pre>";
  //      print_r(get_required_files());
    }

}