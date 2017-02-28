<?php
/**
 * @category  Aligent
 * @package   zipmoney
 * @author    Andi Han <andi@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Referred extends Mage_Core_Block_Template
{
    const REFERRED_HEADER = 'payment/zipmoney_messages/referred_header';
    const REFERRED_BODY = 'payment/zipmoney_messages/referred_body';

    public function getHeadText()
    {
        $vPath = self::REFERRED_HEADER;
        $vText = $this->__(Mage::getStoreConfig($vPath));
        if (!$vText) {
            $vText = $this->__('Your application has been referred');
        }
        return $vText;
    }

    public function getBodyText()
    {
        $vPath = self::REFERRED_BODY;
        $vText = $this->__(Mage::getStoreConfig($vPath));
        if (!$vText) {
            $vText = $this->__('Your application is currently under review by zipMoney and will be processed very shortly.You can contact the customer care at customercare@zipmoney.com.au for any enquiries');
        }
        return $vText;
    }
}