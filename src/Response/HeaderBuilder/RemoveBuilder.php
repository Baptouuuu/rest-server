<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Identity,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\SetInterface;

interface RemoveBuilder
{
    /**
     * @return SetInterface<Header>
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): SetInterface;
}
