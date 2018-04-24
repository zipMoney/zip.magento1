<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Source_PaymentMode
{
  /**
   * Returns the payment action option array.
   *
   * @return array
   */
  public function toOptionArray() {
      return array(
          array(
              'value' => 'authorise',
              'label' => Mage::helper('core')->__('Authorise')
          ),
          array(
              'value' => 'capture',
              'label' => Mage::helper('core')->__('Capture')
          )
      );
  }

}
