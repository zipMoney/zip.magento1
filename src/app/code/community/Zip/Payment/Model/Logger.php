<?php

/**
 * Lagger model to handle debug logs
 * Extends Mage_Core_Model_Logger
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Model_Logger
{
    const CONFIG_DEBUG_PRIVATE_DATA_KEYS_PATH = 'payment/zip_payment/debug/log_private_data_keys';

    protected $_config = null;
    protected $_privateDataKeys = null;

    /**
     * get configuration model
     *
     * @return Zip_Payment_Model_Config
     */
    protected function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = Mage::getSingleton('zip_payment/config');
        }

        return $this->_config;
    }

    /**
     * Returns private Data Keys
     *
     * @return int
     */
    public function getPrivateDataKeys()
    {
        if ($this->_privateDataKeys === null) {
            $this->_privateDataKeys = explode(
                ',',
                (string) $this->getConfig()->getValue(self::CONFIG_DEBUG_PRIVATE_DATA_KEYS_PATH)
            );
        }

        return $this->_privateDataKeys;
    }

    /**
     * Writes the log into log file with given log_level
     *
     * @param  string $message
     * @param  int    $level
     * @param  string $file
     * @param  bool   $forceLog
     * @return void
     */
    public function log($message, $level = Zend_Log::INFO, $file = '', $forceLog = true)
    {
        if (!$this->getConfig()->isLogEnabled() || $level > $this->getConfig()->getLogLevel()) {
            return;
        }

        $file = $this->getConfig()->getLogFile();

        $debugData = $this->sanitizeDebugData($message);
        Mage::log($debugData, $level, $file, $forceLog);
    }

    /**
     * Recursive filter data by replacing sensitive information
     *
     * @param  mixed $debugData
     * @return mixed
     */
    protected function sanitizeDebugData($debugData)
    {
        if (is_array($debugData) && is_array($this->getPrivateDataKeys())) {
            foreach ($debugData as $key => $value) {
                if (in_array(
                    $key, (array) $this->getPrivateDataKeys()
                )
                ) {
                    $debugData[$key] = '****';
                } else {
                    if (is_array($debugData[$key])) {
                        $debugData[$key] = $this->sanitizeDebugData($debugData[$key]);
                    }
                }
            }
        }

        return $debugData;
    }

    /**
     * magic getter for debug functions
     */
    public function __call($name, $arguments)
    {
        $message = implode(' ', $arguments);
        $logLevel = Zend_Log::DEBUG;

        switch($name) {
            case 'alert': $logLevel = Zend_Log::ALERT;
                break;
            case 'emergency': $logLevel = Zend_Log::EMERG;
                break;
            case 'critical': $logLevel = Zend_Log::CRIT;
                break;
            case 'error': $logLevel = Zend_Log::ERR;
                break;
            case 'warn': $logLevel = Zend_Log::WARN;
                break;
            case 'notice': $logLevel = Zend_Log::NOTICE;
                break;
            case 'info': $logLevel = Zend_Log::INFO;
                break;
            case 'debug': $logLevel = Zend_Log::DEBUG;
                break;
            default:
                break;
        }

        $this->log($message, $logLevel);
    }

    public function logException(Exception $e)
    {
        Mage::logException($e);
    }

}
