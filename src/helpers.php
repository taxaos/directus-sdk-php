<?php

if (!function_exists('get_user_timezone')) {
    function get_user_timezone()
    {
        return 'UTC';
    }
}

if (!function_exists('to_name_value')) {
    function to_name_value($array, $keys = null)
    {
        $data = [];
        foreach ($array as $name => $value) {
            $row = ['name' => $name, 'value' => $value];
            if (isset($keys)) $row = array_merge($row, $keys);
            array_push($data, $row);
        }

        return $data;
    }
}