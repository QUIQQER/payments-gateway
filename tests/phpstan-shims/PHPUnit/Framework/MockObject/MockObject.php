<?php

declare(strict_types=1);

namespace PHPUnit\Framework\MockObject;

if (!interface_exists(MockObject::class)) {
    interface MockObject
    {
        public function expects(mixed $matcher): InvocationMocker;

        public function method(string $name): InvocationMocker;
    }
}
