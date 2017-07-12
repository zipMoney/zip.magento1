<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Referred extends Mage_Core_Block_Template
{
  /**
   * @const string
   */
  const REFERRED_HEADER = 'payment/zipmoney_messages/referred_header';
  /**
   * @const string
   */
  const REFERRED_BODY = 'payment/zipmoney_messages/referred_body';
  
  /**
   * Returns the error body text.
   *
   * @return string
   */
  public function getHeadText()
  {
    $text = $this->__(Mage::getStoreConfig(self::REFERRED_HEADER));
    if (!$text) {
      $text = $this->__('Your application has been referred');
    }
    return $text;
  }

  /**
   * Referred Body Text
   *
   * @return string
   */
  public function getBodyText()
  {
    $text = $this->__(Mage::getStoreConfig(self::REFERRED_BODY));
    if (!$text) {
      $text = $this->__('Your application is currently under review by zipMoney and will be processed very shortly.You can contact the customer care at customercare@zipmoney.com.au for any enquiries');
    }
    return $text;
  }
}