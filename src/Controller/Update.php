<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Format,
    Gateway,
    Identity,
    Definition\HttpResource,
    Definition\Access,
    Response\HeaderBuilder\UpdateBuilder,
    HttpResource\HttpResource as Resource,
    Serializer\RequestDecoder,
    Serializer\Denormalizer\HttpResource as ResourceDenormalizer,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
};
use Innmind\Immutable\MapInterface;

final class Update implements Controller
{
    private $decode;
    private $gateways;
    private $denormalize;
    private $format;
    private $buildHeader;

    public function __construct(
        RequestDecoder $decode,
        Format $format,
        ResourceDenormalizer $denormalize,
        MapInterface $gateways,
        UpdateBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->decode = $decode;
        $this->gateways = $gateways;
        $this->denormalize = $denormalize;
        $this->format = $format;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        $update = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceUpdater();

        $update(
            $definition,
            $identity,
            $resource = ($this->denormalize)(
                ($this->decode)($request),
                $definition,
                new Access(Access::UPDATE)
            )
        );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            Headers::of(
                ...($this->buildHeader)($request, $definition, $identity, $resource)
            )
        );
    }
}
