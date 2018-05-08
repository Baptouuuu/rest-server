<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Format,
    Gateway,
    Response\HeaderBuilder\ListBuilder,
    RangeExtractor\Extractor,
    SpecificationBuilder\Builder,
    Request\Range,
    Exception\RangeNotFound,
    Exception\NoFilterFound,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Exception\Http\RangeNotSatisfiable,
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class Index implements Controller
{
    private $format;
    private $serializer;
    private $extractRange;
    private $buildSpecification;
    private $gateways;
    private $buildHeader;

    public function __construct(
        Format $format,
        SerializerInterface $serializer,
        MapInterface $gateways,
        ListBuilder $headerBuilder,
        Extractor $rangeExtractor,
        Builder $specificationBuilder
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

        $this->format = $format;
        $this->serializer = $serializer;
        $this->extractRange = $rangeExtractor;
        $this->buildSpecification = $specificationBuilder;
        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        if (!is_null($identity)) {
            throw new LogicException;
        }

        try {
            $range = ($this->extractRange)($request);
        } catch (RangeNotFound $e) {
            $range = null;
        }

        try {
            $specification = ($this->buildSpecification)($request, $definition);
        } catch (NoFilterFound $e) {
            $specification = null;
        }

        $access = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceListAccessor();
        $identities = $access($definition, $specification, $range);

        if (
            $identities->size() === 0 &&
            $range instanceof Range
        ) {
            throw new RangeNotSatisfiable;
        }

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get(
                $range instanceof Range ? 'PARTIAL_CONTENT' : 'OK'
            )),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            Headers::of(
                ...($this->buildHeader)(
                    $identities,
                    $request,
                    $definition,
                    $specification,
                    $range
                )
            ),
            new StringStream(
                $this->serializer->serialize(
                    $identities,
                    $this->format->acceptable($request)->name(),
                    [
                        'request' => $request,
                        'definition' => $definition,
                        'specification' => $specification,
                        'range' => $range,
                    ]
                )
            )
        );
    }
}
