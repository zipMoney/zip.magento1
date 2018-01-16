<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_Tagline extends Zipmoney_ZipmoneyPayment_Block_Advert_Abstract
{ 
  /**
   * @const string
   */ 
	const PAYMENT_ADVERT_TAGLINE_ACTIVE = 'tagline_active';

	/**
   * Check if tagline is set to be displayed
   *
   * @return bool
   */
	public function isShow()
	{
		if (!$this->_isActive()) {
			return false;
		}

		$currentPage = $this->_getCurrentPageType();
		
		if (!$currentPage) {
			return false;
		}

	return $this->_isTaglineActive($currentPage);	
	}
	/**
   * Check if taglie is active
   *
   * @return bool
   */
	protected function _isTaglineActive($currentPage)
	{
		$path = self::PAYMENT_ADVERT_PREFIX . $currentPage . '_page/'.self::PAYMENT_ADVERT_TAGLINE_ACTIVE;
		return (bool)Mage::getStoreConfig($path);
	}
}