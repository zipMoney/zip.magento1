<?php
/**
 * @category  Aligent
 * @package   zipmoney
 * @author    Andi Han <andi@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_Abstract extends Mage_Core_Block_Template
{
	const PAYMENT_ADVERT_PREFIX = 'payment/zipmoney_advert_';

	/**
   * Check if Zipmoney is enabled from config
   *
   * @return bool
   */
  protected function _isActive()
  {
    // If API keys are empty, should regard the module as disabled.
    $active = Mage::getStoreConfig(Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_ZIPMONEY_PAYMENT_ACTIVE);
    if ($active) {
      $private_key = Mage::getStoreConfig(Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_ZIPMONEY_PAYMENT_KEY);
      $public_key = Mage::getStoreConfig(Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY);
      if ($public_key) {
        return true;
      }
    }
    return false;
  }

	protected function _getCurrentPageType()
	{
		$oRequest = Mage::app()->getRequest();
		$vModule = $oRequest->getModuleName();
		if ($vModule == 'cms') {
				$vId = Mage::getSingleton('cms/page')->getIdentifier();
				$iPos = strpos($vId, 'home');
				if ($iPos === 0) {
						return 'home';
				}
		}else if ($vModule == 'catalog') {
				$vController = $oRequest->getControllerName();
				if ($vController == 'product') {
						return 'product';
				} else if ($vController == 'category') {
						return 'category';
				}
		} else if ($vModule == 'checkout') {
				$vController = $oRequest->getControllerName();
				if ($vController == 'cart') {
						return 'cart';
				}
		}
		return '';
	}
}