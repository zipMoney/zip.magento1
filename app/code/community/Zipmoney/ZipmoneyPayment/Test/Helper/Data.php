<?php


/**
 * Class Zipmoney_ZipmoneyPayment_Test_Model_Charge
 * @loadSharedFixture scope.yaml
 */
class Zipmoney_ZipmoneyPayment_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
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

    $this->_data = Mage::helper('zipmoneypayment');
  }

  public function tearDown()
  {
    @session_write_close();
  }

  /**
   * @test
   */
  public function testVersion(){
    $this->assertEquals($this->_data->getExtensionVersion(),"1.0.0");
  }
  
}