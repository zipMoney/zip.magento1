<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_Widget extends Zipmoney_ZipmoneyPayment_Block_Advert_Abstract
{	
	/**
   * @const string
   */ 
	const PAYMENT_ADVERT_WIDGET_ACTIVE = 'widget_active';

	/**
   * Check if widget is set to be displayed
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

	return $this->_isWidgetActive($currentPage);	
	}
	/**
   * Check if widget is active
   *
   * @return bool
   */
	protected function _isWidgetActive($currentPage)
	{
		$path = self::PAYMENT_ADVERT_PREFIX . $currentPage . '_page/'.self::PAYMENT_ADVERT_WIDGET_ACTIVE;
		return (bool)Mage::getStoreConfig($path);
	}

}