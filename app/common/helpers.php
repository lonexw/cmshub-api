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