<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResource as HttpResourceInterface,
    Identity,
    Action,
    Router,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header\Location,
    Header\LocationValue,
    Header,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class CreateLocationBuilder implements CreateBuilder
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        Identity $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): SetInterface {
        return Set::of(
            Header::class,
            new Location(
                new LocationValue(
                    $this->router->generate(
                        Action::get(),
                        $definition,
                        $identity
                    )
                )
            )
        );
    }
}
