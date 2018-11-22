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
 * Class Zipmoney_ZipmoneyPayment_Test_Model_Charge
 * @loadSharedFixture scope.yaml
 */
class Zipmoney_ZipmoneyPayment_Test_Model_Charge extends EcomDev_PHPUnit_Test_Case
{

    private $_charge;

    private $_chargesApi;

    private $_refundsApi;


    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getApi
     * @loadFixture quotes.yaml
     * @group Zipmoney_ZipmoneyPayment
     */
    public function setUp()
    {
        @session_start();

        include_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(1);

        $this->_mockSessionCookie('customer/session');
        $this->_mockSessionCookie('core/session');
        $this->_mockSessionCookie('checkout/session');

        $this->_chargesApi = $this->getMock('\zipMoney\Api\ChargesApi');

        $this->_refundsApi = $this->getMock('\zipMoney\Api\RefundsApi');

        $this->_charge = Mage::getSingleton('zipmoneypayment/charge');

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
     * @cover Zipmoney_ZipmoneyPayment_Model_Checkout_getApi
     * @group Zipmoney_ZipmoneyPayment
     */
    public function testGetChargesApi()
    {
        $this->_charge->setApi("\zipMoney\Api\ChargesApi");
        $this->assertTrue($this->_charge->getApi() instanceof \zipMoney\Api\ChargesApi);
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

        $this->_charge->setQuote($quote);

        $this->assertTrue($this->_charge->getQuote() instanceof Mage_Sales_Model_Quote);
        $this->assertEquals($this->_charge->getQuote()->getId(), $quoteId);
    }


    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_charge
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture products.yaml
     * @loadFixture customers.yaml
     * @loadFixture orders.yaml
     * @loadFixture order_items.yaml
     * @loadFixture order_addresses.yaml
     * @dataProvider dataProvider
     */
    public function testChargeAuthorise($storeId,$orderId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $this->_charge = Mage::getModel('zipmoneypayment/charge');

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->_charge->setOrder($order);

        $charge = new \zipMoney\Model\Charge;

        $charge->setId("112343");
        $charge->setState("authorised");

        $this->_chargesApi->expects($this->any())
            ->method('chargesCreate')
            ->willReturn($charge);

        $this->_charge->setApi($this->_chargesApi);
        $response = $this->_charge->charge();

        $this->assertEquals($response->getState(), "authorised");
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_charge
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture products.yaml
     * @loadFixture customers.yaml
     * @loadFixture orders.yaml
     * @loadFixture order_items.yaml
     * @loadFixture order_addresses.yaml
     * @dataProvider dataProvider
     */
    public function testChargeCapture($storeId,$orderId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $this->_charge = Mage::getModel('zipmoneypayment/charge');

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->_charge->setOrder($order);

        $charge = new \zipMoney\Model\Charge;

        $charge->setId("112343");
        $charge->setState("captured");

        $this->_chargesApi->expects($this->any())
            ->method('chargesCreate')
            ->willReturn($charge);

        $this->_charge->setApi($this->_chargesApi);
        $response = $this->_charge->charge();

        $this->assertEquals($response->getState(), "captured");
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_captureCharge
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture products.yaml
     * @loadFixture customers.yaml
     * @loadFixture orders.yaml
     * @loadFixture order_items.yaml
     * @loadFixture order_addresses.yaml
     * @dataProvider dataProvider
     */
    public function testCaptureCharge($storeId,$orderId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $this->_charge = Mage::getModel('zipmoneypayment/charge');

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->_charge->setOrder($order);

        $charge = new \zipMoney\Model\Charge;

        $charge->setId("112343");
        $charge->setState("captured");

        $this->_chargesApi->expects($this->any())
            ->method('chargesCapture')
            ->willReturn($charge);

        $this->_charge->setApi($this->_chargesApi);
        $response = $this->_charge->captureCharge(100);

        $this->assertEquals($response->getState(), "captured");
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_cancelCharge
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture products.yaml
     * @loadFixture customers.yaml
     * @loadFixture orders.yaml
     * @loadFixture order_items.yaml
     * @loadFixture order_addresses.yaml
     * @dataProvider dataProvider
     */
    public function testCancelCharge($storeId,$orderId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $this->_charge = Mage::getModel('zipmoneypayment/charge');

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->_charge->setOrder($order);

        $charge = new \zipMoney\Model\Charge;

        $charge->setId("112343");
        $charge->setState("cancelled");

        $this->_chargesApi->expects($this->any())
            ->method('chargesCancel')
            ->willReturn($charge);

        $this->_charge->setApi($this->_chargesApi);
        $response = $this->_charge->cancelCharge();

        $this->assertEquals($response->getState(), "cancelled");
    }


    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_refundCharge
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture products.yaml
     * @loadFixture customers.yaml
     * @loadFixture orders.yaml
     * @loadFixture order_items.yaml
     * @loadFixture order_addresses.yaml
     * @dataProvider dataProvider
     */
    public function testRefundCharge($storeId,$orderId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $this->_charge = Mage::getModel('zipmoneypayment/charge');

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->_charge->setOrder($order);

        $refund = new \zipMoney\Model\Charge;

        $refund->setId("112343");
        $refund->setState("refunded");

        $this->_charge->setOrder($order);

        $this->_refundsApi->expects($this->any())
            ->method('refundsCreate')
            ->willReturn($refund);

        $this->_charge->setApi($this->_refundsApi);

        $response = $this->_charge->refundCharge(1000, "Refund");

        $this->assertEquals($response->getState(), "refunded");
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_charge
     * @group Zipmoney_ZipmoneyPayment
     * @expectedException  Exception
     * @expectedExceptionMessage The order does not exist.
     * @dataProvider dataProvider
     */
    public function testChargeRaisesExceptionOrderDoesnotExist($storeId,$orderId)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->_charge->setOrder($order);

        $this->_charge->setApi($this->_chargesApi);
        $this->_charge->charge();
    }

    /**
     * @test
     * @cover Zipmoney_ZipmoneyPayment_Model_Charge_placeOrder
     * @group Zipmoney_ZipmoneyPayment
     * @loadFixture customers.yaml
     * @loadFixture products.yaml
     * @loadFixture quotes.yaml
     * @loadFixture quote_payments.yaml
     * @loadFixture quote_items.yaml
     * @loadFixture quote_addresses.yaml
     * @expectedException  Exception
     * @expectedExceptionMessage An error occurred order while placing order.
     * The order has not been approved by zipMoney.
     * @dataProvider dataProvider
     */
    public function testPlaceOrderRaisesException($quoteId)
    {
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $this->_charge->setQuote($quote);

        $customer = Mage::getSingleton("customer/session");
        $customer->setCustomer(Mage::getSingleton("customer/customer")->load(1));

        $order = $this->_charge->placeOrder();

        $this->assertNotNull($order->getId());
        $this->assertEquals(Mage_Sales_Model_Order::STATE_NEW, $order->getState());
    }
}