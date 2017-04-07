<?php

/**
 * Class Zipmoney_ZipmoneyPayment_Model_StoreScope
 *
 * @method int getMerchantId()
 * @method Zipmoney_ZipmoneyPayment_Model_StoreScope setMerchantId()
 * @method string getMerchantKey()
 * @method Zipmoney_ZipmoneyPayment_Model_StoreScope setMerchantKey()
 * @method string getScope()
 * @method Zipmoney_ZipmoneyPayment_Model_StoreScope setScope()
 * @method int getScopeId()
 * @method Zipmoney_ZipmoneyPayment_Model_StoreScope setScopeId()
 * @method int getStoreId()
 * @method Zipmoney_ZipmoneyPayment_Model_StoreScope setStoreId()
 */
class Zipmoney_ZipmoneyPayment_Model_StoreScope extends Varien_Object
{
    /**
     * merchant_id
     * merchant_key
     * scope    - default/websites/stores
     * scope_id
     * store_id - from quote/order
     * matched_scopes
     */

    protected $_aMatchedScopes = null;

    /**
     * get matched scopes by merchant_id
     *
     * @param null $iMerchantId
     * @return array|null
     */
    public function getMatchedScopes($iMerchantId = null)
    {
        if (!$this->_aMatchedScopes) {
            if ($iMerchantId === null) {
                $iMerchantId = $this->getMerchantId();
            }
            $this->_aMatchedScopes = $this->_getScopesByMerchantId($iMerchantId);
        }
        return $this->_aMatchedScopes;
    }

    /**
     * get Scopes (scope/scope_id) on websites level
     *
     * @param $iMerchantId
     * @return array|null
     */
    protected function _getScopesByMerchantId($iMerchantId)
    {
        if (!$iMerchantId) {
            return null;
        }

        $aMatched = array();
        $cWebsites = Mage::app()->getWebsites();
        foreach ($cWebsites as $oWebsite) {
            $vPath = Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_ZIPMONEY_PAYMENT_ID;
            $iWebMId = Mage::app()->getWebsite($oWebsite->getId())->getConfig($vPath);

            if ($iMerchantId != $iWebMId) {
                continue;
            }

            /**
             * todo: will need to check if zipMoney is enabled on this website
             */

            $aScope = array(
                'scope' => 'websites',
                'scope_id' => $oWebsite->getId(),
            );
            $aMatched[] = $aScope;
        }

        $this->_aMatchedScopes = $aMatched;
        return $this->_aMatchedScopes;
    }

    /**
     * get current scope
     *
     * @return array
     */
    public function getCurrentScope()
    {
        if (!$this->getScope()) {
            $vWebsiteCode = Mage::app()->getRequest()->getParam('website');
            if ($vWebsiteCode) {
                // from magento admin
                $oWebsite = Mage::getModel('core/website')->load($vWebsiteCode);
                $this->setScope('websites');
                $this->setScopeId($oWebsite->getId());
            } else {
                // get scope based on current merchant_id (when 'configuration_updated' notification comes)
                $aMatched = $this->_getScopesByMerchantId($this->getMerchantId());
                if ($aMatched && is_array($aMatched) && count($aMatched)) {
                    foreach ($aMatched as $aItem) {
                        // get/return the first matched scope
                        $this->setScope($aItem['scope']);
                        $this->setScopeId($aItem['scope_id']);
                        break;
                    }
                } else {
                    // from frontend
                    $oWebsite = Mage::app()->getWebsite();
                    $this->setScope('websites');
                    $this->setScopeId($oWebsite->getId());
                }
            }
        }
        $aScope = array(
            'scope' => $this->getScope(),
            'scope_id' => $this->getScopeId(),
        );
        return $aScope;
    }
}
