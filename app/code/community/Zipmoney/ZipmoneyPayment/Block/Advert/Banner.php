<?php
/**
 * @category  Aligent
 * @package   zipmoney
 * @author    Andi Han <andi@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_Banner extends Zipmoney_ZipmoneyPayment_Block_Advert_Abstract
{
	const PAYMENT_ADVERT_BANNER_ACTIVE = 'banner_active';

	public function isShow()
	{
		if (!$this->_isActive()) {
			return false;
		}

		$currentPage = $this->_getCurrentPageType();
		
		if (!$currentPage) {
			return false;
		}

	return $this->_isBannerActive($currentPage);	
	}
	
	protected function _isBannerActive($currentPage)
	{
		$path = self::PAYMENT_ADVERT_PREFIX . $currentPage . '_page/'.self::PAYMENT_ADVERT_BANNER_ACTIVE;
		return (bool)Mage::getStoreConfig($path);
	}

}