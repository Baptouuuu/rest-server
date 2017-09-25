<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\Request\Range;
use Innmind\Http\Message\ServerRequest;

interface ExtractorInterface
{
    /**
     * Extract a Range out of the request
     *
     * @param ServerRequest $request
     *
     * @throws RangeNotFoundException
     *
     * @return Range
     */
    public function extract(ServerRequest $request): Range;
}
