<?php

declare(strict_types=1);

namespace PHPUnit\Framework;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

if (!class_exists(TestCase::class)) {
    abstract class TestCase
    {
        protected function setUp(): void
        {
        }

        protected function tearDown(): void
        {
        }

        /**
         * @template T of object
         * @param class-string<T> $className
         * @return MockObject&T
         */
        final protected function createMock(string $className): MockObject
        {
            throw new LogicException('PHPStan analysis shim');
        }

        /** @param class-string<Throwable> $exception */
        final protected function expectException(string $exception): void
        {
        }

        final protected function addToAssertionCount(int $count): void
        {
        }

        final public static function once(): mixed
        {
            return null;
        }

        final public static function callback(callable $callback): mixed
        {
            return null;
        }

        /** @param class-string $className */
        final public static function isInstanceOf(string $className): mixed
        {
            return null;
        }

        /**
         * @template T of object
         * @param class-string<T> $expected
         * @phpstan-assert T $actual
         */
        final public static function assertInstanceOf(string $expected, mixed $actual): void
        {
        }

        final public static function assertFalse(mixed $condition): void
        {
        }

        final public static function assertNotSame(mixed $expected, mixed $actual): void
        {
        }

        /** @phpstan-assert !false $actual */
        final public static function assertNotFalse(mixed $actual): void
        {
        }

        final public static function assertNull(mixed $actual): void
        {
        }

        final public static function assertSame(mixed $expected, mixed $actual): void
        {
        }

        final public static function assertStringContainsString(string $needle, string $haystack): void
        {
        }

        final public static function assertTrue(mixed $condition): void
        {
        }
    }
}
