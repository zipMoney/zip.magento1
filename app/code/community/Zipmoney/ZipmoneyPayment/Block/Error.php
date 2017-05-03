<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Error extends Mage_Core_Block_Template
{  
  /**
   * @const string
   */
  const SERVICE_UNAVAILABLE_HEADING = 'payment/zipmoney_messages/service_unavailable_heading';
  /**
   * @const string
   */
  const SERVICE_UNAVAILABLE_BODY = 'payment/zipmoney_messages/service_unavailable_body';

  /**
   * Returns the error heading text.
   *
   * @return string
   */
  public function getHeadText()
  {
    $text = $this->__(Mage::getStoreConfig(self::SERVICE_UNAVAILABLE_HEADING));
    if (!$text) {
      $text = $this->__('The service is unavailable');
    }
    return $text;
  }

  /**
   * Returns the error body text.
   *
   * @return string
   */
  public function getBodyText()
  {
    $text = $this->__(Mage::getStoreConfig(self::SERVICE_UNAVAILABLE_BODY));
    if (!$text) {
      $text = $this->__('Unfortunately the zipMoney - Buy now, Pay later service is currently unavailable. We will be right back and apologies for the inconvenience.');
    }
    return $text;
  }

  /**
   * Returns the error type text.
   *
   * @return string
   */
  public function getErrorTypeText()
  {
    try {
      $iCode = (int)$this->getRequest()->getParam('code');
    } catch (Exception $e) {
      $iCode = 0;
    }
    switch($iCode)
    {
      case 0:
        $vText = $this->__('General Error');
        break;
      case 400:
        $vText = $this->__('400 Bad Request');
        break;
      case 401:
        $vText = $this->__('401 Unauthorized');
        break;
      case 403:
        $vText = $this->__('403 Forbidden');
        break;
      case 404:
        $vText = $this->__('404 Not Found');
        break;
      case 409:
        $vText = $this->__('409 Conflict');
        break;
      default:
        $vText = $this->getRequest()->getParam('code') . $this->__(' General Error');
        break;
    }
    return $vText;
  }
}