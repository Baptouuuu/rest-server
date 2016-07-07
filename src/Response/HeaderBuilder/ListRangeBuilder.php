<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface,
    Header\AcceptRanges,
    Header\AcceptRangesValue,
    Header\ContentRange,
    Header\ContentRangeValue
};
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map
};

final class ListRangeBuilder implements ListBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(
        SetInterface $identities,
        ServerRequestInterface $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): MapInterface {
        $map = new Map('string', HeaderInterface::class);

        if (!$definition->isRangeable()) {
            return $map;
        }

        $map = $map->put(
            'Accept-Ranges',
            new AcceptRanges(new AcceptRangesValue('resources'))
        );

        if (!$range instanceof Range) {
            return $map;
        }

        $length = $range->lastPosition() - $range->firstPosition();

        return $map->put(
            'Content-Range',
            new ContentRange(
                new ContentRangeValue(
                    'resources',
                    $range->firstPosition(),
                    $last = $range->firstPosition() + $identities->size(),
                    $identities->size() < $length ? $last : $last + $length
                )
            )
        );
    }
}
