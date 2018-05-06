<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Routing\Route,
    Routing\Name,
    Definition,
    Action,
};
use Innmind\UrlTemplate\Template;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private $definition;

    public function setUp()
    {
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Identity('uuid'),
            new Map('string', Definition\Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Definition\Gateway('foo'),
            false,
            new Map('string', 'string')
        );
    }

    public function testInterface()
    {
        $route = new Route(
            Action::get(),
            $template = Template::of('/foo'),
            $name = new Name('foo'),
            $this->definition
       );

        $this->assertSame(Action::get(), $route->action());
        $this->assertSame($template, $route->template());
        $this->assertSame($name, $route->name());
        $this->assertSame($this->definition, $route->definition());
    }

    /**
     * @dataProvider cases
     */
    public function testOf($action, $expected)
    {
        $route = Route::of(
            $action,
            new Name('foo.bar.baz'),
            $this->definition
        );

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($expected, (string) $route->template());
    }

    public function cases(): array
    {
        return [
            [Action::list(), '{/prefix}/foo/bar/baz/'],
            [Action::get(), '{/prefix}/foo/bar/baz/{identity}'],
            [Action::create(), '{/prefix}/foo/bar/baz/'],
            [Action::update(), '{/prefix}/foo/bar/baz/{identity}'],
            [Action::remove(), '{/prefix}/foo/bar/baz/{identity}'],
            [Action::link(), '{/prefix}/foo/bar/baz/{identity}'],
            [Action::unlink(), '{/prefix}/foo/bar/baz/{identity}'],
            [Action::options(), '{/prefix}/foo/bar/baz/'],
        ];
    }
}
