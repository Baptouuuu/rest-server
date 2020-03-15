<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Exception;

use Innmind\Immutable\Map;

class HttpResourceDenormalizationException extends RuntimeException
{
    private Map $errors;

    public function __construct(Map $errors)
    {
        if (
            (string) $errors->keyType() !== 'string' ||
            (string) $errors->valueType() !== DenormalizationException::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Map<string, %s>',
                DenormalizationException::class
            ));
        }

        $this->errors = $errors;
        parent::__construct('The input resource is not denormalizable');
    }

    /**
     * @return Map<string, DenormalizationException>
     */
    public function errors(): Map
    {
        return $this->errors;
    }
}
