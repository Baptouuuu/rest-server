<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Translator\Specification;

use Innmind\Rest\Server\{
    Translator\Specification\DefaultTranslator,
    Translator\SpecificationTranslator,
    Specification\Filter,
};
use Innmind\Url\QueryInterface;
use PHPUnit\Framework\TestCase;

class DefaultTranslatorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            SpecificationTranslator::class,
            new DefaultTranslator
        );
    }

    public function testTranslate()
    {
        $translate = new DefaultTranslator;

        $spec = (new Filter('range', [0, 42]))
            ->and(new Filter('foo', 'bar'));

        $query = $translate($spec);

        $this->assertInstanceOf(QueryInterface::class, $query);
        $this->assertSame(
            'range%5B0%5D=0&range%5B1%5D=42&foo=bar',
            (string) $query
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\SpecificationNotUsableAsQuery
     */
    public function testThrowWhenOrConditionFound()
    {
        $translate = new DefaultTranslator;

        $spec = (new Filter('range', [0, 42]))
            ->or(new Filter('foo', 'bar'));

        $translate($spec);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\SpecificationNotUsableAsQuery
     */
    public function testThrowWhenNotConditionFound()
    {
        $translate = new DefaultTranslator;

        $spec = (new Filter('range', [0, 42]))->not();

        $translate($spec);
    }
}
