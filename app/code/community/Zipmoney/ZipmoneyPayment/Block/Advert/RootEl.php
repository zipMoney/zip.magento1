<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_RootEl extends Zipmoney_ZipmoneyPayment_Block_Advert_Abstract
{
  /**
   * Sets the template file
   */
  public function _prepareLayout()
  {               
    if ($this->_isActive()){
      $this->setTemplate('zipmoney/zipmoneypayment/advert/root_el.phtml');
    }
  }
}