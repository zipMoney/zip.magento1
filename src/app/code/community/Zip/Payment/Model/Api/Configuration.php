<?php

/**
 * Configuration Model of Payment API
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Configuration;

class Zip_Payment_Model_Api_Configuration
{
    public function __construct()
    {
        // require autoload from Zip Payment SDK
        include_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';
    }

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('zip_payment');
    }

    /**
     * generate API configuration
     *
     * @param  string $storeId Store ID
     * @return object
     */
    public function generateApiConfiguration($storeId = null)
    {
        try {
            $apiConfig = Configuration::getDefaultConfiguration();
            $config = $this->getHelper()->getConfig();
            $magentoVersion = Mage::getVersion();
            $extensionVersion = $this->getHelper()->getCurrentVersion();

            $apiConfig
                ->setApiKey(
                    'Authorization',
                    Mage::helper('core')
                        ->decrypt($config->getValue(Zip_Payment_Model_Config::CONFIG_PRIVATE_KEY_PATH, $storeId))
                )
                ->setEnvironment($config->getValue(Zip_Payment_Model_Config::CONFIG_ENVIRONMENT_PATH, $storeId))
                ->setApiKeyPrefix('Authorization', 'Bearer')
                ->setPlatform("Magento/{$magentoVersion} Zip_Payment/{$extensionVersion}")
                ->setCurlTimeout((int) $config->getValue(Zip_Payment_Model_Config::CONFIG_API_TIMEOUT_PATH, $storeId));

            if ($config->isDebugEnabled() && $config->isLogEnabled() && $config->getLogLevel() >= Zend_Log::DEBUG) {
                $apiConfig
                    ->setDebug(true)
                    ->setDebugFile(Mage::getBaseDir('log') . DS . $config->getLogFile());
            }

            $apiConfig->setDefaultHeaders();

            return $apiConfig;
        } catch(Exception $e) {
            throw $e;
        }

    }

}
