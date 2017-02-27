<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Link;

use Innmind\Rest\Server\Link\{
    Parameter,
    ParameterInterface
};
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testInterface()
    {
        $parameter = new Parameter('foo', 42);

        $this->assertInstanceOf(ParameterInterface::class, $parameter);
        $this->assertSame('foo', $parameter->name());
        $this->assertSame(42, $parameter->value());
    }
}
