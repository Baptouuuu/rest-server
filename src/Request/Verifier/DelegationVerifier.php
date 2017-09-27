<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

final class DelegationVerifier implements Verifier
{
    private $verifiers;

    public function __construct(MapInterface $verifiers)
    {
        if (
            (string) $verifiers->keyType() !== 'int' ||
            (string) $verifiers->valueType() !== Verifier::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<int, %s>',
                Verifier::class
            ));
        }

        $this->verifiers = $verifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(
        ServerRequest $request,
        HttpResource $definition
    ) {
        $this
            ->verifiers
            ->keys()
            ->sort(function(int $a, int $b) {
                return $a < $b;
            })
            ->foreach(function(int $index) use ($request, $definition) {
                $this->verifiers->get($index)->verify($request, $definition);
            });
    }
}
