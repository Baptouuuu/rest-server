<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

interface CreateBuilderInterface
{
    /**
     * @param IdentityInterface $identity
     * @param ServerRequest $request
     * @param HttpResource $definition
     * @param HttpResourceInterface $resource
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        IdentityInterface $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): MapInterface;
}
