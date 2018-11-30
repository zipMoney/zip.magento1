<?php

/**
 * @category  Zip.co Payment
 * @package   Zip.co Payment Magento1.x Extension
 * @author    Zip.co Integration Team <integrations@zip.co>
 * @copyright 2018 zip Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zip.co/
 */

class Zipmoney_ZipmoneyPayment_Model_Logger
{
    /**
     * Log Setting
     * @const
     */
    const LOG_SETTING = 'payment/zipmoneypayment/log_setting';

    /**
     * Log File Path
     * @const
     */
    const LOG_FILE_PATH = 'payment/zipmoney_developer_settings/log_file';

    /**
     * Log File
     * @const
     */
    const DEFAULT_LOG_FILE_NAME = 'zipMoney-Payment.log';

    //performance improve to check config once
    protected $isEnabled = null;
    protected $logFile = null;
    protected $logLevel = null;

    //privacy protection
    protected $protectedKeys = array("phone");

    public function addDebugProctectedKeys(array $keys)
    {
        if (is_array($keys)) {
            $this->protectedKeys = array_merge($this->protectedKeys, $keys);
        }

        return $this;
    }

    /**
     * Checks if log is enabled
     *
     * @param int $storeId
     * @return boolean
     */
    public function isLogEnabled($storeId = null)
    {
        if ($this->isEnabled === null) {
            $enabled = -1;
            //base on Mage::log so if Mage::log disabled log is disabled
            $mageLogEnabled = $this->isMageLogEnable();
            if ($mageLogEnabled) {
                $enabled = Mage::getStoreConfig(self::LOG_SETTING, $storeId);
            }

            $this->isEnabled = $enabled >= 0 ? true : false;
        }

        return $this->isEnabled;
    }

    /**
     * Check dependance function
     *
     * @return boolean
     */
    public function isMageLogEnable()
    {
        $enabled = Mage::getStoreConfig('dev/log/active');
        return (bool) $enabled;
    }

    /**
     * Returns the log level
     *
     * @param int $storeId
     * @return int
     */
    public function getConfigLogLevel($storeId = null)
    {
        if ($this->logLevel === null) {
            $logLevel = Mage::getStoreConfig(self::LOG_SETTING, $storeId);

            if ($logLevel === null || $logLevel < 0) {
                $logLevel = Zend_Log::INFO;
            }

            $this->logLevel = $logLevel;
        }

        return $this->logLevel;
    }

    /**
     * Returns the log file
     *
     * @param int $storeId
     * @return int
     */
    public function getLogFile($storeId = null)
    {
        if ($this->logFile === null) {
            $logFileName = Mage::getStoreConfig(self::LOG_FILE_PATH, $storeId);
            if (!$logFileName) {
                $logFileName = self::DEFAULT_LOG_FILE_NAME;
            }

            $this->logFile = $logFileName;
        }

        return $this->logFile;
    }

    /**
     * Writes the log into log file with given log_level
     *
     * @param $message
     * @param int $level
     * @param null $storeId
     */
    public function log($message, $level = Zend_Log::DEBUG, $storeId = null)
    {
        if (!$this->isLogEnabled($storeId)) {
            return;
        }

        $configLevel = $this->getConfigLogLevel($storeId);
        $file = $this->getLogFile($storeId);
        if ($level > $configLevel) {
            return;
        }

        $debugData = $this->sanitizeData($message);

        Mage::log($debugData, $level, $file);
    }

    /**
     * Recursive replace sensitive information in log file
     *
     * @return string/array
     */
    public function sanitizeData($debugData)
    {
        if (is_array($debugData)) {
            foreach ($debugData as $key => $value) {
                if (in_array($key, $this->protectedKeys)) {
                    $debugData[$key] = '****';
                } else {
                    if (is_array($debugData[$key])) {
                        $debugData[$key] = $this->sanitizeData($debugData[$key]);
                    }
                }
            }
        }

        return $debugData;
    }

    /**
     * Writes the log into log file with debug log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function debug($message, $storeId = null)
    {
        $this->log($message, Zend_Log::DEBUG, $storeId);
    }

    /**
     * Writes the log into log file with info log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function info($message, $storeId = null)
    {
        $this->log($message, Zend_Log::INFO, $storeId);
    }

    /**
     * Writes the log into log file with info log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function warn($message, $storeId = null)
    {
        $this->log($message, Zend_Log::WARN, $storeId);
    }

    /**
     * Writes the log into log file with info log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function notice($message, $storeId = null)
    {
        $this->log($message, Zend_Log::NOTICE, $storeId);
    }

    /**
     * Writes the log into log file with error log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function error($message, $storeId = null)
    {
        $this->log($message, Zend_Log::ERR, $storeId);
    }

    /**
     * Writes the log into log file with critical log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function critical($message, $storeId = null)
    {
        $this->log($message, Zend_Log::CRIT, $storeId);
    }

    /**
     * Writes the log into log file with emergency log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function emergency($message, $storeId = null)
    {
        $this->log($message, Zend_Log::EMERG, $storeId);
    }

    /**
     * Writes the log into log file with alert log_level
     *
     * @param $message
     * @param null $storeId
     */
    public function alert($message, $storeId = null)
    {
        $this->log($message, Zend_Log::ALERT, $storeId);
    }

}
