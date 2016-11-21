<?php

namespace Directus\SDK;

use Directus\SDK\Response\EntryCollection;
use Directus\SDK\Response\Entry;
use Directus\Util\ArrayUtils;
use Directus\Util\StringUtils;

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

    protected function parseData($tableName, array $data)
    {
        $method = 'parse' . StringUtils::underscoreToCamelCase($tableName, true);
        if (method_exists($this, $method)) {
            $data = call_user_func_array([$this, $method], [$data]);
        }

        return $data;
    }

    public function parseDirectusUsers($data)
    {
        $data = ArrayUtils::omit($data, ['id', 'user', 'access_token', 'last_login', 'last_access', 'last_page']);
        if (ArrayUtils::has($data, 'password')) {
            // @TODO: use Auth hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        return $data;
    }
}