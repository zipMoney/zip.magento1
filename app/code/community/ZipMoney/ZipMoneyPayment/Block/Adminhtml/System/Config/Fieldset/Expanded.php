<?php

class Zipmoney_ZipmoneyPayment_Block_Adminhtml_System_Config_Fieldset_Expanded extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    protected function _getCollapseState($element) {

        if ($this->_isBannerSettings($element)) {
            return false;
        }
        $extra = Mage::getSingleton('admin/session')->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        return true;
    }

    protected function _isBannerSettings($oElement)
    {
        $vId = $oElement->getId();
        if ($vId == 'payment_zipmoney_marketing_banners'
            || $vId == 'payment_zipmoney_home_page_banner'
            || $vId == 'payment_zipmoney_product_page_banner'
            || $vId == 'payment_zipmoney_category_page_banner'
            || $vId == 'payment_zipmoney_cart_page_banner'
            ) {
            return true;
        }
        return false;
    }
}
