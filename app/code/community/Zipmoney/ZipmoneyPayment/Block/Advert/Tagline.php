<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Integration team
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
     * Check tagline should be display
     *
     * @return boolean
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
     * check tagline is active by pages
     *
     * @param string $currentPage
     * @return void
     */
    protected function _isTaglineActive($currentPage)
    {
        $path = self::PAYMENT_ADVERT_PREFIX . $currentPage . '_page/'.self::PAYMENT_ADVERT_TAGLINE_ACTIVE;
        return (bool) Mage::getStoreConfig($path);
    }
}