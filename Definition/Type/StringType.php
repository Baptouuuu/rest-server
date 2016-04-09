<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\TypeInterface,
    Exception\DenormalizationException,
    Exception\NormalizationException
};
use Innmind\Immutable\{
    CollectionInterface,
    SetInterface,
    Set
};

class StringType implements TypeInterface
{
    private static $identifiers;

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(CollectionInterface $config): TypeInterface
    {
        return new self;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data)
    {
        if (!is_string($data)) {
            throw new DenormalizationException('The value must be a string');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data)
    {
        if (!is_string($data)) {
            throw new NormalizationException('The value must be a string');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public static function identifiers(): SetInterface
    {
        if (self::$identifiers === null) {
            self::$identifiers = (new Set('string'))->add('string');
        }

        return self::$identifiers;
    }
}
