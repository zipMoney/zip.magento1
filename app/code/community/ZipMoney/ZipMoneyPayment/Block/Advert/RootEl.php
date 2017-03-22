<?php
/**
 * @category  Aligent
 * @package   zipmoney
 * @author    Andi Han <andi@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Advert_RootEl extends Zipmoney_ZipmoneyPayment_Block_Advert_Abstract
{
   
    public function _prepareLayout()
    {               

      if ($this->_isActive()){
        $this->setTemplate('zipmoney/zipmoneypayment/advert/root_el.phtml');
      }
    }
}