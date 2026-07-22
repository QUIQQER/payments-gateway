<?php

declare(strict_types=1);

namespace QUI\Tests\ERP\Payments\Example\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Accounting\Payments\Gateway\Gateway;
use QUI\ERP\Accounting\Payments\Transactions\Transaction;
use QUI\ERP\Currency\Currency;
use QUI\ERP\Order\AbstractOrder;
use QUI\ERP\Payments\Example\Payment;
use QUI\ERP\Payments\Example\Provider\Payments;

final class PaymentTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $requestBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestBackup = $_REQUEST;
        $_REQUEST = [];
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->requestBackup;

        parent::tearDown();
    }

    public function testPaymentExposesExampleMetadataAndCapabilities(): void
    {
        $Payment = new Payment();

        self::assertNotSame('', $Payment->getTitle());
        self::assertNotSame('', $Payment->getDescription());
        self::assertTrue($Payment->isGateway());
        self::assertFalse($Payment->isUnique());
        self::assertTrue($Payment->refundSupport());
        self::assertSame([Payment::class], (new Payments())->getPaymentTypes());
    }

    public function testUnknownOrderIsNotSuccessful(): void
    {
        self::assertFalse((new Payment())->isSuccessful('phpunit-payments-gateway-missing-order'));
    }

    public function testCanceledGatewayPaymentRedirectsToTheOrder(): void
    {
        $_REQUEST['canceled'] = '1';

        $Gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrderUrl'])
            ->getMock();
        $Gateway->expects(self::once())
            ->method('getOrderUrl')
            ->willReturn('/order');

        ob_start();
        (new Payment())->executeGatewayPayment($Gateway);
        ob_end_clean();
    }

    public function testGatewayPaymentPurchasesTheOrder(): void
    {
        $_REQUEST['amount'] = '12.50';

        $Currency = $this->createMock(Currency::class);
        $Currency->method('getCode')->willReturn('EUR');

        $Order = $this->createMock(AbstractOrder::class);
        $Order->method('getCurrency')->willReturn($Currency);
        $Order->method('getHash')->willReturn('phpunit-order-hash');
        $Order->expects(self::once())
            ->method('setPaymentData')
            ->with('payment-test-gateway-order', 'test-value');
        $Order->expects(self::once())->method('update');

        $Gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrder', 'purchase'])
            ->getMock();
        $Gateway->method('getOrder')->willReturn($Order);
        $Gateway->expects(self::once())
            ->method('purchase')
            ->with(
                12.5,
                $Currency,
                $Order,
                self::isInstanceOf(Payment::class),
                self::callback(static fn(array $data): bool => isset($data['payment'], $data['title']))
            )
            ->willReturn($this->createMock(Transaction::class));

        (new Payment())->executeGatewayPayment($Gateway);
    }

    public function testRefundFailureIsHandledByTheExample(): void
    {
        $Transaction = $this->createMock(Transaction::class);
        $Transaction->method('getHash')->willThrowException(new QUI\Exception('Expected test failure'));

        (new Payment())->refund($Transaction, 10.0);

        $this->addToAssertionCount(1);
    }
}
