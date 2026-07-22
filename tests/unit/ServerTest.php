<?php

declare(strict_types=1);

namespace QUI\Tests\ERP\Payments\Example\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Accounting\ArticleList;
use QUI\ERP\Accounting\Payments\Gateway\Gateway;
use QUI\ERP\Order\AbstractOrder;
use QUI\ERP\Payments\Example\Server\Server;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class ServerTest extends TestCase
{
    public function testOnRequestIgnoresUnrelatedRequests(): void
    {
        $post = $_POST;
        $_POST = [];

        try {
            Server::onRequest($this->createMock(QUI\Rewrite::class), '');
        } finally {
            $_POST = $post;
        }

        $this->addToAssertionCount(1);
    }

    public function testEmptyRequestIsIgnored(): void
    {
        self::assertNull(Server::handleRequest([]));
    }

    public function testUnrelatedPostRequestIsIgnored(): void
    {
        self::assertNull(Server::handleRequest(['unrelated' => 'value']));
    }

    public function testMissingOrderHashRedirectsToTheSiteRoot(): void
    {
        $Response = Server::handleRequest(['PAYMENT_TEST_GATEWAY' => '1']);

        self::assertInstanceOf(RedirectResponse::class, $Response);
        self::assertSame(URL_DIR, $Response->getTargetUrl());
    }

    public function testCancelRedirectUsesTheTrustedGatewayUrl(): void
    {
        $Gateway = $this->gatewayMock();
        $Gateway->method('getOrder')->willReturn($this->createMock(AbstractOrder::class));
        $Gateway->method('getCancelUrl')->willReturn('/trusted-cancel');

        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order',
            'submit' => 'CANCEL',
            'cancelUrl' => 'https://attacker.invalid/'
        ], $Gateway);

        self::assertInstanceOf(RedirectResponse::class, $Response);
        self::assertSame('/trusted-cancel', $Response->getTargetUrl());
    }

    public function testMissingOrderRedirectsToTheSiteRoot(): void
    {
        $Gateway = $this->gatewayMock();
        $Gateway->method('getOrder')->willReturn(null);

        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order'
        ], $Gateway);

        self::assertInstanceOf(RedirectResponse::class, $Response);
        self::assertSame(URL_DIR, $Response->getTargetUrl());
    }

    public function testCancelWithoutGatewayUrlsRedirectsToTheSiteRoot(): void
    {
        $Gateway = $this->gatewayMock();
        $Gateway->method('getOrder')->willReturn($this->createMock(AbstractOrder::class));
        $Gateway->method('getCancelUrl')->willReturn('');
        $Gateway->method('getOrderUrl')->willReturn('');

        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order',
            'submit' => 'CANCEL'
        ], $Gateway);

        self::assertInstanceOf(RedirectResponse::class, $Response);
        self::assertSame(URL_DIR, $Response->getTargetUrl());
    }

    public function testInvalidCalculatedAmountIsRejected(): void
    {
        $Gateway = $this->gatewayWithCalculatedAmount('invalid');
        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order',
            'submit' => 'PAY'
        ], $Gateway);

        self::assertInstanceOf(Response::class, $Response);
        self::assertSame(Response::HTTP_BAD_REQUEST, $Response->getStatusCode());
    }

    public function testNonPositiveCalculatedAmountIsRejected(): void
    {
        $Gateway = $this->gatewayWithCalculatedAmount(0);
        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order',
            'submit' => 'PAY'
        ], $Gateway);

        self::assertInstanceOf(Response::class, $Response);
        self::assertSame(Response::HTTP_BAD_REQUEST, $Response->getStatusCode());
    }

    public function testPayRedirectUsesTheServerCalculatedAmount(): void
    {
        $Gateway = $this->gatewayWithCalculatedAmount('12.50');
        $Gateway->expects(self::once())
            ->method('getGatewayUrl')
            ->with([
                Gateway::URL_PARAM_GATEWAY_PAYMENT => 1,
                Gateway::URL_PARAM_USER_REDIRECTED => 0,
                'amount' => 12.5
            ])
            ->willReturn('/trusted-gateway');

        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order',
            'submit' => 'PAY',
            'pay' => '0.01'
        ], $Gateway);

        self::assertInstanceOf(RedirectResponse::class, $Response);
        self::assertSame('/trusted-gateway', $Response->getTargetUrl());
    }

    public function testInitialGatewayRequestRendersTheOrder(): void
    {
        $Gateway = $this->gatewayWithCalculatedAmount(12.5);
        $Response = Server::handleRequest([
            'PAYMENT_TEST_GATEWAY' => '1',
            'orderHash' => 'phpunit-order'
        ], $Gateway);

        self::assertInstanceOf(Response::class, $Response);
        self::assertSame(Response::HTTP_OK, $Response->getStatusCode());
        self::assertStringContainsString('#123', $Response->getContent());
        self::assertStringContainsString('Rendered article list', $Response->getContent());
    }

    private function gatewayMock(): Gateway
    {
        $Gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setOrder', 'getOrder', 'getCancelUrl', 'getOrderUrl', 'getGatewayUrl'])
            ->getMock();
        $Gateway->expects(self::once())
            ->method('setOrder')
            ->with('phpunit-order');

        return $Gateway;
    }

    private function gatewayWithCalculatedAmount(mixed $amount): Gateway
    {
        $Articles = $this->createMock(ArticleList::class);
        $Articles->expects(self::once())->method('hideHeader');
        $Articles->expects(self::once())->method('calc');
        $Articles->method('toArray')->willReturn([
            'calculations' => [
                'sum' => $amount
            ]
        ]);
        $Articles->method('toHTML')->willReturn('<p>Rendered article list</p>');

        $Order = $this->createMock(AbstractOrder::class);
        $Order->method('getArticles')->willReturn($Articles);
        $Order->method('getId')->willReturn(123);
        $Order->method('getHash')->willReturn('phpunit-order-hash');

        $Gateway = $this->gatewayMock();
        $Gateway->method('getOrder')->willReturn($Order);

        return $Gateway;
    }
}
