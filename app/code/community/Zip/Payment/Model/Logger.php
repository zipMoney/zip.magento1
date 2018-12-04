<?php

class Zip_Payment_Model_Logger extends Mage_Core_Model_Logger
{
    const CONFIG_DEVELOPER_LOG_ACTIVE_PATH = 'dev/log/active';
    const CONFIG_DEBUG_LOG_LEVEL_PATH = 'payment/zip_payment/debug/log_level';
    const CONFIG_DEBUG_PRIVATE_DATA_KEYS_PATH = 'payment/zip_payment/debug/log_private_data_keys';
    const CONFIG_DEBUG_LOG_FILE_PATH = 'payment/zip_payment/debug/log_file';

    const DEFAULT_LOG_FILE_NAME = 'zip_payment.log';

    protected $config = null;
    protected $enabled = null;
    protected $logLevel = null;
    protected $logFile = null;
    protected $privateDataKeys = null;

    protected function getConfig() {

        if($this->config === null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }

        return $this->config;
    }

    public function isEnabled() {

        if($this->enabled == null) {

            if($this->getConfig()->isDebugEnabled()) {
                $isLogActive = $this->getConfig()->getFlag(self::CONFIG_DEVELOPER_LOG_ACTIVE_PATH);
                $logLevel = $this->getLogLevel();
                $this->enabled = $isLogActive && $logLevel >= 0;
            }

            $this->enabled = false;
        }

        return $this->enabled;
    }

    /**
     * Returns the log level
     *
     * @return int
     */
    public function getAllowedLogLevel()
    {
        if ($this->logLevel === null) {
            $this->logLevel = $this->getConfig()->getValue(self::CONFIG_DEBUG_LOG_LEVEL_PATH);
        }

        return $this->logLevel;
    }

    /**
     * Returns the log file
     *
     * @return string
     */
    public function getLogFile()
    {
        if ($this->logFile === null) {
            $logFileName = $this->getConfig()->getValue(self::LOG_FILE_PATH);

            if (empty($logFileName)) {
                $logFileName = self::DEFAULT_LOG_FILE_NAME;
            }

            $this->logFile = $logFileName;
        }

        return $this->logFile;
    }

    /**
     * Returns private Data Keys
     *
     * @return int
     */
    public function getPrivateDataKeys()
    {
        if ($this->privateDataKeys === null) {
            $this->privateDataKeys = explode(',', (string)$this->getConfig()->getValue(self::CONFIG_DEBUG_PRIVATE_DATA_KEYS_PATH));
        }

        return $this->privateDataKeys;
    }

    /**
     * Writes the log into log file with given log_level
     *
     * @param string $message
     * @param int $level
     * @param string $file
     * @param bool $forceLog
     * @return void
     */
    public function log($message, $level = Zend_Log::DEBUG, $file = '', $forceLog = true)
    {
        if (!$this->isEnabled() || $level > $this->getAllowedLogLevel()) {
            return;
        }

        $file = $this->getLogFile();
        $debugData = $this->sanitizeDebugData($data);
        parent::log($debugData, $level, $file, $forceLog);
    }

    /**
     * Recursive filter data by replacing sensitive information
     *
     * @param mixed $debugData
     * @return mixed
     */
    protected function sanitizeDebugData($debugData)
    {
        if (is_array($debugData) && is_array($this->getPrivateDataKeys())) {
            foreach ($debugData as $key => $value) {
                if (in_array($key, $this->getPrivateDataKeys())) {
                    $debugData[$key] = '****';
                }
                else {
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
            case 'alert': $logLevel = Zend_Log::ALERT; break;
            case 'emergency': $logLevel = Zend_Log::EMERG; break;
            case 'critical': $logLevel = Zend_Log::CRIT; break;
            case 'error': $logLevel = Zend_Log::ERR; break;
            case 'warn': $logLevel = Zend_Log::WARN; break;
            case 'notice': $logLevel = Zend_Log::NOTICE; break;
            case 'info': $logLevel = Zend_Log::INFO; break;
            case 'debug': $logLevel = Zend_Log::DEBUG; break;
            default: break;
        }

        $this->log($message, $logLevel);
    }

}
