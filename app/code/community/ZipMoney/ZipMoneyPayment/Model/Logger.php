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
	const LOG_ENABLED = 'payment/zipmoney_developer_settings/log_enabled';
	const LOG_LEVEL = 'payment/zipmoney_developer_settings/log_level';
	const LOG_FILE_PATH = 'payment/zipmoney_developer_settings/log_file';
	const LOG_FILE_NAME = 'zipMoney-Payment.log';

	public function isLogEnabled($iStoreId = null)
	{
		$vPath = self::LOG_ENABLED;
		if ($iStoreId !== null) {
				$iEnabled = Mage::app()->getStore($iStoreId)->getConfig($vPath);
		} else {
				$iEnabled = Mage::getModel('zipmoneypayment/config')->getConfigByCurrentScope($vPath);
		}
		return true;
		return $iEnabled ? true : false;
	}


	public function getConfigLogLevel($iStoreId = null)
	{
		$vPath = self::LOG_LEVEL;
		if ($iStoreId !== null) {
				$iConfigLevel = Mage::app()->getStore($iStoreId)->getConfig($vPath);
		} else {
				$iConfigLevel = Mage::getModel('zipmoneypayment/config')->getConfigByCurrentScope($vPath);
		}
		if ($iConfigLevel === null) {
				$iConfigLevel = Zend_log::INFO;
		}
		return $iConfigLevel;
	}

	public function getLogFile($iStoreId = null)
	{
		$vPath = self::LOG_FILE_PATH;
		if ($iStoreId !== null) {
				$vFileName = Mage::app()->getStore($iStoreId)->getConfig($vPath);
		} else {
				$vFileName = Mage::getModel('zipmoneypayment/config')->getConfigByCurrentScope($vPath);
		}
		if (!$vFileName) {
				$vFileName = self::LOG_FILE_NAME;
		}

		return $vFileName;
	}

	/**
	 * Write log into log file with log_level
	 *
	 * @param $vMessage
	 * @param int $iLevel
	 * @param null $iStoreId
	 */
	public function log($message, $level = Zend_log::INFO, $storeId = null)
	{
		if (!$this->isLogEnabled($storeId)) {
				return;
		}
		$configLevel = $this->getConfigLogLevel($storeId);

		// errors are always logged.
		if ($configLevel < 3) {
				$configLevel = Zend_log::DEBUG; // default log level
		}

		$file = $this->getLogFile($storeId);

		if ($level > $configLevel) {
				return;
		}

		Mage::log($message, $level, $file);
	}


	public function debug($message, $storeId = null)
	{
		$this->log($message, Zend_log::DEBUG, $storeId);
	}

	public function info($message, $storeId = null)
	{
		$this->log($message, Zend_log::INFO, $storeId);
	}

	public function warn($message, $storeId = null)
	{
		$this->log($message, Zend_log::WARN, $storeId);
	}

	public function notice($message, $storeId = null)
	{
		$this->log($message, Zend_log::NOTICE, $storeId);
	}

	public function error($message, $storeId = null)
	{
		$this->log($message, Zend_log::ERR, $storeId);
	}

	public function critical($message, $storeId = null)
	{
		$this->log($message, Zend_log::CRIT, $storeId);
	}

	public function emergency($message, $storeId = null)
	{
		$this->log($message, Zend_log::EMERG, $storeId);
	}

	public function alert($message, $storeId = null)
	{
		$this->log($message, Zend_log::ALERT, $storeId);
	}

}