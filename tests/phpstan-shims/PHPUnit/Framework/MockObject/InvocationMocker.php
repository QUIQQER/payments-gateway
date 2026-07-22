<?php

declare(strict_types=1);

namespace PHPUnit\Framework\MockObject;

use Throwable;

if (!class_exists(InvocationMocker::class)) {
    class InvocationMocker
    {
        public function method(string $name): self
        {
            return $this;
        }

        public function with(mixed ...$arguments): self
        {
            return $this;
        }

        public function willReturn(mixed ...$values): self
        {
            return $this;
        }

        public function willThrowException(Throwable $Throwable): self
        {
            return $this;
        }
    }
}
