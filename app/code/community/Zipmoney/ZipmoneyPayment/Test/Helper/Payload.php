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
 * Class Zipmoney_ZipmoneyPayment_Test_Model_Charge
 * @loadSharedFixture scope.yaml
 */
class Zipmoney_ZipmoneyPayment_Test_Helper_Payload extends EcomDev_PHPUnit_Test_Case
{   

  
  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getApi   
   * @loadFixture quotes.yaml
   * @group Zipmoney_ZipmoneyPayment
   */
  public function setUp()
  {     
    @session_start();

    set_include_path(get_include_path() . PATH_SEPARATOR . Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor');
    require_once(Mage::getBaseDir('lib') . DS . 'Zipmoney' . DS . 'vendor' . DS . 'autoload.php');
    
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(1);

    $this->_payload = Mage::helper('zipmoneypayment/payload');
  }


  protected function _mockSessionCookie($sessionName)
  {
    $sessionMock = $this->getModelMock($sessionName, array('init'));
    $sessionMock->expects($this->any())
        ->method('init')
        ->will($this->returnSelf());

    $this->replaceByMock('singleton', $sessionName, $sessionMock);
    $this->replaceByMock('model', $sessionName, $sessionMock);
   
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

  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Helper_Payload_getCheckoutPayload
   * @group Zipmoney_ZipmoneyPayment     
   * @loadFixture customers.yaml      
   * @loadFixture products.yaml      
   * @loadFixture quotes.yaml
   * @loadFixture quote_payments.yaml      
   * @loadFixture quote_items.yaml
   * @loadFixture quote_addresses.yaml   
   * @dataProvider dataProvider
   */
  public function testGetCheckoutPayload($quoteId)
  {
  
    $quote = Mage::getModel('sales/quote')->load($quoteId);
    $checkoutPayload = $this->_payload->getCheckoutPayload($quote);
    
  }
  
}