<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\CatchActionNotImplemented,
    Controller,
    Identity,
    Definition,
    Exception\ActionNotImplemented,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    ProtocolVersion,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class CatchActionNotImplementedTest extends TestCase
{
    private $definition;

    public function setUp(): void
    {
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Gateway('foo'),
            new Definition\Identity('foo'),
            Set::of(Definition\Property::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            new CatchActionNotImplemented($this->createMock(Controller::class))
        );
    }

    public function testReturnResponseWhenActionNotImplemented()
    {
        $catch = new CatchActionNotImplemented(
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));
        $identity = $this->createMock(Identity::class);
        $controller
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->will($this->throwException(new ActionNotImplemented));

        $response = $catch($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(405, $response->statusCode()->value());
        $this->assertSame('Method Not Allowed', $response->reasonPhrase()->toString());
    }

    public function testReturnControllerResponse()
    {
        $catch = new CatchActionNotImplemented(
            $controller = $this->createMock(Controller::class)
        );
        $request = $this->createMock(ServerRequest::class);
        $identity = $this->createMock(Identity::class);
        $controller
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->willReturn($expected = $this->createMock(Response::class));

        $this->assertSame(
            $expected,
            $catch($request, $this->definition, $identity)
        );
    }
}
