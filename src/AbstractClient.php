<?php

namespace Directus\SDK;

use Directus\SDK\Response\EntryCollection;
use Directus\SDK\Response\Entry;
use Directus\Util\ArrayUtils;

abstract class AbstractClient implements RequestsInterface
{
    // @TODO: move to a builder class
    protected function createResponseFromData($data)
    {
        if (isset($data['rows']) || (isset($data['data']) && ArrayUtils::isNumericKeys($data['data']))) {
            $response = new EntryCollection($data);
        } else {
            $response = new Entry($data);
        }

        return $response;
    }
}