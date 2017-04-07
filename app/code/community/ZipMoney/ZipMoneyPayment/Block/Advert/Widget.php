<?php
/**
 * @category  Aligent
 * @package   zipmoney
 * @author    Andi Han <andi@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_Widget extends Zipmoney_ZipmoneyPayment_Block_Advert_Abstract
{	
	const PAYMENT_ADVERT_WIDGET_ACTIVE = 'widget_active';

	public function isShow()
	{
		if (!$this->_isActive()) {
			return false;
		}

		$currentPage = $this->_getCurrentPageType();
		
		if (!$currentPage) {
			return false;
		}

	return $this->_isWidgetActive($currentPage);	
	}
	
	protected function _isWidgetActive($currentPage)
	{
		$path = self::PAYMENT_ADVERT_PREFIX . $currentPage . '_page/'.self::PAYMENT_ADVERT_WIDGET_ACTIVE;
		return (bool)Mage::getStoreConfig($path);
	}

}