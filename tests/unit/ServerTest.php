<?php

declare(strict_types=1);

namespace QUI\Tests\ERP\Payments\Example\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Payments\Example\Server\Server;

final class ServerTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $postBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->postBackup = $_POST;
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;

        parent::tearDown();
    }

    public function testEmptyRequestIsIgnored(): void
    {
        Server::onRequest($this->createMock(QUI\Rewrite::class), '');

        $this->addToAssertionCount(1);
    }

    public function testUnrelatedPostRequestIsIgnored(): void
    {
        $_POST['unrelated'] = 'value';

        Server::onRequest($this->createMock(QUI\Rewrite::class), '');

        $this->addToAssertionCount(1);
    }
}
