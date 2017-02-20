<?php


/**
 * Class Zipmoney_ZipmoneyPayment_Test_Model_Observer
 * @loadSharedFixture scope.yaml
 */
class Zipmoney_ZipmoneyPayment_Test_Model_Standard_Checkout extends EcomDev_PHPUnit_Test_Case
{   
  private $_checkout;
  
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

    $quote = Mage::getModel('sales/quote')->load(103);

    $this->_checkout = Mage::getSingleton('zipmoneypayment/standard_checkout', array('quote' => $quote));
    $this->_apiHelper = $this->getMock('\zipMoney\Client\Api\CheckoutsApi');

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
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getApi   
   * @group Zipmoney_ZipmoneyPayment
   */
  public function testGetApi()
  {       

    $this->assertTrue($this->_checkout->getApi() instanceof \zipMoney\Client\Api\CheckoutsApi);
  }
  
  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_setApi
   * @group Zipmoney_ZipmoneyPayment
   */
  public function testSetApi()
  { 
    $this->_checkout->setApi('\zipMoney\Client\Api\ChargesApi');
    $this->assertTrue($this->_checkout->getApi() instanceof \zipMoney\Client\Api\ChargesApi);
  }

  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getQuote
   * @group Zipmoney_ZipmoneyPayment
   * @loadFixture quotes.yaml
   * @dataProvider dataProvider
   */
  public function testSetAndGetQuote($storeId,$quoteId)
  {    
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

    $quote = Mage::getModel('sales/quote')->load($quoteId);

    $this->_checkout->setQuote($quote);

    $this->assertTrue($this->_checkout->getQuote() instanceof Mage_Sales_Model_Quote);
    $this->assertEquals($this->_checkout->getQuote()->getId(),$quoteId);
  }

  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getQuote   
   * @group Zipmoney_ZipmoneyPayment     
   * @loadFixture products.yaml
   * @loadFixture customers.yaml
   * @loadFixture quotes.yaml
   * @loadFixture quote_items.yaml
   * @loadFixture quote_addresses.yaml   
   * @dataProvider dataProvider
   */
  public function testStart($storeId,$quoteId)
  {
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

    $quote = Mage::getModel('sales/quote')->load($quoteId);
    
    $this->_checkout->setQuote($quote);

    $checkout = new \zipMoney\Model\Checkout;

    $return_url = "https://account.zipmoney.com.au/?ch=ch_f8h2sz09na";
    $checkout_id = "ch_f8h2sz09na";
    $checkout->setUri($return_url);
    $checkout->setId($checkout_id);

    $this->_apiHelper->expects($this->any())
                  ->method('checkoutsCreate')
                  ->willReturn( $checkout  );
    
    $this->_checkout->setApi($this->_apiHelper);
    $this->_checkout->start("checkout");


    $this->assertEquals($this->_checkout->getCheckoutId(),$checkout_id);
    $this->assertEquals($this->_checkout->getRedirectUrl(),$return_url);
  }
  
  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getQuote   
   * @group Zipmoney_ZipmoneyPayment  
   * @expectedException  Exception
   * @expectedExceptionMessage The quote does not exist.
   * @dataProvider dataProvider
   */
  public function testStartRaisesExceptionQuoteDoesnotExist($storeId,$quoteId)
  {
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

    $quote = Mage::getModel('sales/quote')->load($quoteId);
    
    $this->_checkout->setQuote($quote);
    
    $this->_checkout->setApi($this->_apiHelper);    
    $this->_checkout->start("checkout");
  }


   /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getQuote   
   * @group Zipmoney_ZipmoneyPayment  
   * @expectedException  Exception
   * @expectedExceptionMessage Cannot get redirect URL from zipMoney.
   * @loadFixture products.yaml
   * @loadFixture customers.yaml
   * @loadFixture quotes.yaml
   * @loadFixture quote_items.yaml
   * @loadFixture quote_addresses.yaml   
   * @dataProvider dataProvider
   */
  public function testStartRaisesExceptionRedirectUrl($storeId,$quoteId)
  {
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

    $quote = Mage::getModel('sales/quote')->load($quoteId);
    
    $this->_checkout->setQuote($quote);

    $checkout = new \zipMoney\Model\Checkout;

    $return_url = "https://account.zipmoney.com.au/?ch=ch_f8h2sz09na";
    
    $checkout->error  = new stdClass;

    $this->_apiHelper->expects($this->any())
                  ->method('checkoutsCreate')
                  ->willReturn( $checkout  );
    
    $this->_checkout->setApi($this->_apiHelper);
    $this->_checkout->start("checkout");
  }

  /**
   * @test
   * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getQuote   
   * @group Zipmoney_ZipmoneyPayment  
   * @expectedException  Exception
   * @expectedExceptionMessage Cannot process the order due to zero amount.
   * @loadFixture quotes.yaml
   * @dataProvider dataProvider
   */
  public function testStartRaisesExceptionZeroAmount($storeId,$quoteId)
  {
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

    $quote = Mage::getModel('sales/quote')->load($quoteId);
    
    $this->_checkout->setQuote($quote);
    
    $this->_checkout->setApi($this->_apiHelper);    

    $this->_checkout->start("checkout");
  }
  

}