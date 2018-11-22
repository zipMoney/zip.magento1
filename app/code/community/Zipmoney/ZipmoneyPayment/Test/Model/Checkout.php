<?php
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Integration Team
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */


/**
 * Class Zipmoney_ZipmoneyPayment_Test_Model_Checkout
 * @loadSharedFixture scope.yaml
 */
class Zipmoney_ZipmoneyPayment_Test_Model_Checkout extends EcomDev_PHPUnit_Test_Case
{

    private $_checkout;

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getApi
     * @loadFixture quotes.yaml
     * @group Zipmoney_ZipmoneyPaymenta
     */
    public function setUp()
    {
        @session_start();

        include_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(1);

        $quote = Mage::getModel('sales/quote')->load(103);

        $this->_checkoutsApi = $this->getMock('\zipMoney\Api\CheckoutsApi');
        $this->_checkout = Mage::getSingleton('zipmoneypayment/checkout');
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
    public function testGetCheckoutApi()
    {
        $this->_checkout->setApi("\zipMoney\Api\CheckoutsApi");
        $this->assertTrue($this->_checkout->getApi() instanceof \zipMoney\Api\CheckoutsApi);
    }


    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_start
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture products.yaml
     * @loadFixture customers.yaml
     * @loadFixture quotes.yaml
     * @loadFixture quote_items.yaml
     * @loadFixture quote_addresses.yaml
     * @dataProvider dataProvider
     */
    public function testCheckoutStart($storeId,$quoteId)
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

        $this->_checkoutsApi->expects($this->any())
            ->method('checkoutsCreate')
            ->willReturn($checkout);

        $this->_checkout->setApi($this->_checkoutsApi);
        $this->_checkout->start();

        $this->assertEquals($this->_checkout->getCheckoutId(), $checkout_id);
        $this->assertEquals($this->_checkout->getRedirectUrl(), $return_url);
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_start
     * @group Zipmoney_ZipmoneyPayment
     * @expectedException  Exception
     * @expectedExceptionMessage The quote does not exist.
     * @dataProvider dataProvider
     */
    public function testCheckoutStartRaisesExceptionQuoteDoesnotExist($storeId,$quoteId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        $quote = Mage::getModel('sales/quote')->load($quoteId);

        $this->_checkout->setQuote($quote);
        $this->_checkout->setApi($this->_checkoutsApi);
        $this->_checkout->start();
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_start
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
    public function testCheckoutStartRaisesExceptionRedirectUrl($storeId,$quoteId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        $quote = Mage::getModel('sales/quote')->load($quoteId);

        $this->_checkout->setQuote($quote);

        $checkout = new \zipMoney\Model\Checkout;
        $return_url = "https://account.zipmoney.com.au/?ch=ch_f8h2sz09na";
        $checkout->error  = new stdClass;

        $this->_checkoutsApi->expects($this->any())
            ->method('checkoutsCreate')
            ->willReturn($checkout);
        $this->_checkout->setApi($this->_checkoutsApi);
        $this->_checkout->start();
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_start
     * @group Zipmoney_ZipmoneyPayment
     * @expectedException  Exception
     * @expectedExceptionMessage Cannot process the order due to zero amount.
     * @loadFixture quotes.yaml
     * @dataProvider dataProvider
     */
    public function testCheckoutStartRaisesExceptionZeroAmount($storeId,$quoteId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        $quote = Mage::getModel('sales/quote')->load($quoteId);

        $this->_checkout->setQuote($quote);
        $this->_checkout->setApi($this->_checkoutsApi);
        $this->_checkout->start();
    }
}