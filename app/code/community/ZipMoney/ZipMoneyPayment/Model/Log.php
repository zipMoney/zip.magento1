<?php
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */
class Zipmoney_ZipmoneyPayment_Model_Log extends Mage_Core_Helper_Abstract
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
	public function log($vMessage, $iLevel = Zend_log::INFO, $iStoreId = null)
	{
		if (!$this->isLoggingEnabled($iStoreId)) {
				return;
		}
		$iConfigLevel = $this->getConfigLogLevel($iStoreId);

		// errors are always logged.
		if ($iConfigLevel < 3) {
				$iConfigLevel = Zend_log::INFO; // default log level
		}

		$vFileName = $this->getLogFile($iStoreId);
		
		if ($iLevel > $iConfigLevel) {
				return;
		}
		
		Mage::log($vMessage, $iLevel, $vFileName);
	}
}