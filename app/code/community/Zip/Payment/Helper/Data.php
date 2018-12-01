<?php

use \zipMoney\ApiClient;
use \zipMoney\Configuration;

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';
    const CONFIG_ENVIRONMENT_PATH = 'payment/zip_payment/environment';
    const CONFIG_PRIVATE_KEY_PATH = 'payment/zip_payment/private_key';

    public function isActive() {
        return (bool)Mage::getStoreConfig(self::CONFIG_ACTIVE_PATH);
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

        $config = Configuration::getDefaultConfiguration();

        $config
        ->setApiKey('Authorization', trim(Mage::getStoreConfig(self::CONFIG_PRIVATE_KEY_PATH)))
        ->setEnvironment(trim(Mage::getStoreConfig(self::CONFIG_ENVIRONMENT_PATH)))
        ->setApiKeyPrefix('Authorization', 'Bearer')
        ->setPlatform('Magento/'. Mage::getVersion() . ' Zip_Payment/' . $this->getCurrentVersion());

        return $config;

    }

}
