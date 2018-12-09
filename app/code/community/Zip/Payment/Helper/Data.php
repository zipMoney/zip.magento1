<?php



//use Zip\ApiClient;
//use Zip\Configuration;

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';

    public function isActive() {
        return Mage::getSingleton('zip_payment/config')->getFlag(self::CONFIG_ACTIVE_PATH);
    }

    /**
     * Retrieves the extension version.
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return trim((string) Mage::getConfig()->getNode()->modules->Zip_Payment->version);
    }
    

    public function autoLoad() {
        require_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';
    }

    /**
     * Get current store url
     *
     * @param $route
     * @param $param
     * @return string
     */
    public function getUrl($route, $param = array())
    {
        return Mage::getUrl($route, $param);
    }

}
