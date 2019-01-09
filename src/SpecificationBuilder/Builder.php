<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\SpecificationBuilder;

use Innmind\Rest\Server\{
    Exception\NoFilterFound,
    Definition\HttpResource,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\Specification;

interface Builder
{
    /**
     * Transform request filters into a specification
     *
     * @throws NoFilterFound
     * @throws FilterNotApplicable
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): Specification;
}
