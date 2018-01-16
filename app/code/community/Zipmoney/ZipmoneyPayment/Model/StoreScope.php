<?php
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_StoreScope extends Varien_Object
{
  /**
   * @var string
   */
  protected $_matchedScopes = null;

  /**
   * Returns the matched scopes by merchant_id
   *
   * @param null $merchantId
   * @return array|null
   */
  public function getMatchedScopes($merchantId = null)
  {
    if (!$this->_matchedScopes) {
      if ($merchantId === null) {
          $merchantId = $this->getMerchantId();
      }
      $this->_matchedScopes = $this->_getScopesByMerchantId($merchantId);
    }
    return $this->_matchedScopes;
  }

  /**
   * Returns Scopes (scope/scope_id) on websites level
   *
   * @param $merchantId
   * @return array|null
   */
  protected function _getScopesByMerchantId($merchantId)
  {
    if (!$merchantId) {
      return null;
    }

    $matched = array();
    $websites = Mage::app()->getWebsites();
    foreach ($websites as $website) {
      $path = Zipmoney_ZipmoneyPayment_Model_Config::PAYMENT_ZIPMONEY_PAYMENT_ID;
      $webMid = Mage::app()->getWebsite($website->getId())->getConfig($path);

      if ($merchantId != $webMid) {
          continue;
      }
      /**
       * todo: will need to check if zipMoney is enabled on this website
       */
      $scope = array(
          'scope' => 'websites',
          'scope_id' => $website->getId(),
      );
      $matched[] = $scope;
    }

    $this->_matchedScopes = $matched;
    return $this->_matchedScopes;
  }

  /**
   * Get current scope
   *
   * @return array
   */
  public function getCurrentScope()
  {
    if (!$this->getScope()) {
      $websiteCode = Mage::app()->getRequest()->getParam('website');
      if ($websiteCode) {
        // from magento admin
        $website = Mage::getModel('core/website')->load($websiteCode);
        $this->setScope('websites');
        $this->setScopeId($website->getId());
      } else {
        // get scope based on current merchant_id (when 'configuration_updated' notification comes)
        $matched = $this->_getScopesByMerchantId($this->getMerchantId());
        if ($matched && is_array($matched) && count($matched)) {
          foreach ($matched as $item) {
            // get/return the first matched scope
            $this->setScope($item['scope']);
            $this->setScopeId($item['scope_id']);
            break;
          }
        } else {
          // from frontend
          $website = Mage::app()->getWebsite();
          $this->setScope('websites');
          $this->setScopeId($website->getId());
        }
      }
    }
    $scope = array(
      'scope' => $this->getScope(),
      'scope_id' => $this->getScopeId(),
    );
    return $scope;
  }
}
