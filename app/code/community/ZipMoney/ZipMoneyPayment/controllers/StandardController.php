<?php
use \zipMoney\ApiException;
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_StandardController extends Zipmoney_ZipmoneyPayment_Controller_Abstract
{

  /**
   * Checkout Model
   *
   * @var string
   */
  protected $_checkoutModel = 'zipmoneypayment/checkout';


  /**
   * Start the checkout by requesting the redirect url and checkout id
   */
  public function indexAction()
  {

    if ($this->_expireAjax()) {
      return;
    }

    try {
        //throw new Exception("Error Processing Request", 1);

        if (!$this->getRequest()->isPost()) {
          $this->_ajaxRedirectResponse();
          return;
        }

        $result = $this->getOnepage()->savePayment(array("method" => $this->_config->getMethodCode()));

        $this->_logger->info($this->_helper->__('Starting Checkout'));

        // Initialize the checkout model
        // Start the checkout process
        $this->_initCheckout()->start();

        if($redirectUrl = $this->_checkout->getRedirectUrl()) {
          $this->_logger->info($this->_helper->__('Successful to get redirect url [ %s ] ', $redirectUrl));
          $data = array( 'redirect_uri' => $redirectUrl ,'message'  => $this->_helper->__('Redirecting to zipMoney.'));
          return $this->_sendResponse($data, Mage_Api2_Model_Server::HTTP_OK);
        } else {
          Mage::throwException("Failed to get redirect url.");
        }

    } catch (Mage_Core_Exception $e) {
        $this->_logger->debug($e->getMessage());
    } catch (Exception $e) {
        $this->_logger->debug($e->getMessage());
    }

    $this->_sendResponse(array('message' => $this->_helper->__('Can not get the redirect url from zipMoney.')), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
  }


  public function redirectAction()
  {
    $this->loadLayout();
    $this->renderLayout();
  }

}

?>