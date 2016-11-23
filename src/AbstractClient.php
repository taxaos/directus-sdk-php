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
        $method = 'parseOn' . StringUtils::underscoreToCamelCase($tableName, true);
        if (method_exists($this, $method)) {
            $data = call_user_func_array([$this, $method], [$data]);
        }

        return $data;
    }

    protected function parseFile($path)
    {
        $attributes = [];
        if (file_exists($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $mimeType = mime_content_type($path);
            $attributes['name'] = pathinfo($path, PATHINFO_FILENAME) . '.' . $ext;
            $attributes['type'] = $mimeType;
            $content = file_get_contents($path);
            $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($content);
            $attributes['data'] = $base64;
        } else {
            throw new \Exception('Missing "file" or "data" attribute.');
        }

        return $attributes;
    }

    protected function parseOnDirectusUsers($data)
    {
        $data = ArrayUtils::omit($data, ['id', 'user', 'access_token', 'last_login', 'last_access', 'last_page']);
        if (ArrayUtils::has($data, 'password')) {
            // @TODO: use Auth hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        }

        if (ArrayUtils::has($data, 'avatar_file_id')) {
            $data['avatar_file_id'] = $this->parseFile($data['avatar_file_id']);
        }

        return $data;
    }

    protected function parseOnDirectusFiles($data)
    {
        // @TODO: omit columns such id or user.
        $data = ArrayUtils::omit($data, ['id', 'user']);

        return $data;
    }
}