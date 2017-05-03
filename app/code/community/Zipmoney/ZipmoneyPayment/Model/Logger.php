<?php
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Logger extends Mage_Core_Helper_Abstract
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

	/**
	 * Checks if log is enabled 
	 *
   * @param int $storeId
   * @return boolean
   */
	public function isLogEnabled($storeId = null)
	{
		if ($storeId !== null) {
			$enabled = Mage::app()->getStore($storeId)->getConfig(self::LOG_SETTING);
		} else {
			$enabled = Mage::getModel('zipmoneypayment/config')->getConfigByCurrentScope(self::LOG_SETTING);
		}
		return $iEnabled > 0 ? true : false;
	}

	/**
	 * Returns the log level
	 *
   * @param int $storeId
   * @return int
   */
	public function getConfigLogLevel($storeId = null)
	{
		if ($storeId !== null) {
			$configLevel = Mage::app()->getStore($storeId)->getConfig(self::LOG_SETTING);
		} else {
			$configLevel = Mage::getModel('zipmoneypayment/config')->getConfigByCurrentScope(self::LOG_SETTING);
		}
		if ($configLevel === null || $configLevel < 0 ) {
			$configLevel = Zend_Log::INFO;
		}
		return $configLevel;
	}

	/**
	 * Returns the log file
	 *
   * @param int $storeId
   * @return int
   */
	public function getLogFile($storeId = null)
	{
		if ($storeId !== null) {
				$fileName = Mage::app()->getStore($storeId)->getConfig(self::LOG_FILE_PATH);
		} else {
				$fileName = Mage::getModel('zipmoneypayment/config')->getConfigByCurrentScope(self::LOG_FILE_PATH);
		}
		if (!$fileName) {
				$fileName = self::DEFAULT_LOG_FILE_NAME;
		}

		return $fileName;
	}

	/**
	 * Writes the log into log file with given log_level
	 *
	 * @param $message
	 * @param int $level
	 * @param null $storeId
	 */
	public function log($message, $level = Zend_Log::INFO, $storeId = null)
	{
		if (!$this->isLogEnabled($storeId)) {
			return;
		}
		$configLevel = $this->getConfigLogLevel($storeId);
		// errors are always logged.
		if ($configLevel < 3) {
			$configLevel = Zend_Log::DEBUG; // default log level
		}
		$file = $this->getLogFile($storeId);
		if ($level > $configLevel) {
			return;
		}
		Mage::log($message, $level, $file);
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