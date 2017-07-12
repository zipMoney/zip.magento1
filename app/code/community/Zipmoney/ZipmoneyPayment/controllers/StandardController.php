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
   *
   * @return json
   * @throws Mage_Core_Exception
   */
  public function indexAction()
  {

    if ($this->_expireAjax()) {
      return;
    }

    $exception_message = null;

    try {

      if (!$this->getRequest()->isPost()) {
        $this->_ajaxRedirectResponse();
        return;
      }          


      if($data = $this->getRequest()->getPost('payment', array()))
      {
        $result = $this->getOnepage()->savePayment($data);

        if (empty($result['error'])) {
          $this->_logger->info($this->_helper->__('Payment method saved'));
          $review = $this->getRequest()->getPost('review');
          if(isset($review) && $review == "true"){
            $this->loadLayout('checkout_onepage_review');
            $result['goto_section'] = 'review';
            $result['update_section'] = array(
                'name' => 'review',
                'html' => $this->getLayout()->getBlock('root')->toHtml()
            );
          }
        } else{
          Mage::throwException($this->_helper->__("Failed to save the payment method"));
        }
      }

      $this->_logger->info($this->_helper->__('Starting Checkout'));
      /* 
        -Initialize the checkout model
        -Start the checkout process
      */
      $this->_initCheckout()->start();
      if($redirectUrl = $this->_checkout->getRedirectUrl()) {
        $this->_logger->info($this->_helper->__('Successful to get redirect url [ %s ] ', $redirectUrl));
        $result['redirect_uri'] = $redirectUrl;
        $result['message']  = $this->_helper->__('Redirecting to zipMoney.');
        return $this->_sendResponse($result, Mage_Api2_Model_Server::HTTP_OK);
      } else {
        Mage::throwException("Failed to get redirect url.");
      }
    } catch (Mage_Payment_Exception $e) {
      if ($e->getFields()) {
        $result['fields'] = $e->getFields();
      }
      $result['error'] = $e->getMessage();
    } catch (Mage_Core_Exception $e) {
      $this->_logger->debug($e->getMessage());
      $exception_message = $e->getMessage();
    } catch(\InvalidArgumentException $e){
      $this->_logger->debug($e->getMessage());      
      $result['error'] = "Invalid arguments provided.\n\nError Detail:- ".$e->getMessage();
    } catch (Exception $e) {
      $this->_logger->debug($e->getMessage());
      $exception_message = $e->getMessage();
    }

    if(empty($result['error'])){
      $result['error'] = $this->_helper->__('An error occurred while trying to checkout with zipMoney.');
    }

    if(!is_null($exception_message)){
      $result['exception_message'] = $exception_message;
    }

    $this->_sendResponse($result, Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
  }
}
