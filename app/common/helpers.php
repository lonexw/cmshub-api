<?php

if ( ! function_exists('paginateArray')) {
    function paginateArray($data)
    {
        return [
            'paginatorInfo' => [
                'currentPage' => $data->currentPage(),
                'perPage' => $data->perPage(),
                'total' => $data->total(),
                'lastPage' => $data->lastPage(),
                'count' => $data->count()
            ],
            'data' => $data->items()
        ];
    }
}
if ( ! function_exists('arrayGet')) {
    function arrayGet($array, $key)
    {
        if (is_null($key)) {
            return null;
        }

        if (!isset($array[$key])) {
            return null;
        }
        return $array[$key];
    }
}

if ( ! function_exists('makePassword')) {
    function makePassword($length = 6)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = array('A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        // 在 $chars 中随机取 $length 个数组元素键名
        $keys = array_rand($chars, $length);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 将 $length 个数组元素连接成字符串
            $password .= $chars[$keys[$i]];
        }
        return $password;
    }
}