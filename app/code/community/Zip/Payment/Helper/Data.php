<?php



//use Zip\ApiClient;
//use Zip\Configuration;

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';
    const CONFIG_ENVIRONMENT_PATH = 'payment/zip_payment/environment';
    const CONFIG_PRIVATE_KEY_PATH = 'payment/zip_payment/private_key';

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
    

    public function getAPIClientConfiguration() {

        $this->autoLoad();

        $config = Zip\Configuration::getDefaultConfiguration();

        $config
        ->setApiKey('Authorization', trim(Mage::getSingleton('zip_payment/config')->getValue(self::CONFIG_PRIVATE_KEY_PATH)))
        ->setEnvironment(trim(Mage::getSingleton('zip_payment/config')->getValue(self::CONFIG_ENVIRONMENT_PATH)))
        ->setApiKeyPrefix('Authorization', 'Bearer')
        ->setPlatform('Magento/'. Mage::getVersion() . ' Zip_Payment/' . $this->getCurrentVersion());

        return $config;

    }

    public function autoLoad() {
        require_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';
    }

}
