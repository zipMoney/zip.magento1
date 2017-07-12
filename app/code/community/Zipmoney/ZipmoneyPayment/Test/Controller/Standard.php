<?php
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

/**
 * Class Zipmoney_ZipmoneyPayment_Test_Model_Observer
 * @loadSharedFixture scope.yaml
 */
class Zipmoney_ZipmoneyPayment_Test_Controller_Standard extends EcomDev_PHPUnit_Test_Case_Controller
{   
  
  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getApi   
   * @group Zipmoney_ZipmoneyPayment
   */
  public function setUp()
  {     
    @session_start();

    set_include_path(get_include_path() . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor');
    require_once(Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor' . DS . 'autoload.php');
    
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(1);

   // $quote = Mage::getModel('sales/quote')->load(103);

    //$this->_checkout = Mage::getSingleton('zipmoneypayment/standard_checkout', array('quote' => $quote));
    //$this->_apiHelper = $this->getMock('\zipMoney\Client\Api\CheckoutsApi');

  }

  public function setProtectedProperty($object, $property, $value)
  {
      $reflection = new ReflectionClass($object);
      $reflection_property = $reflection->getProperty($property);
      $reflection_property->setAccessible(true);
      $reflection_property->setValue($object, $value);
  }

  public function tearDown()
  {
    @session_write_close();
  }


  public function testIndexAction()
  {
    //$response = $this->dispatch('zipmoneypayment/standard');
    //$this->assertResponseHttpCode(500);
  }
  

}