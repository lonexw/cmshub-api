<?php

return [
    'oss' => [
        'access_key'    => env('ALIYUN_OSS_ACCESS_KEY', ''),
        'access_secret' => env('ALIYUN_OSS_ACCESS_SECRET', ''),
        'role_arn'      => env('ALIYUN_OSS_ROLE_ARN', ''),
        'region_id'     => env('ALIYUN_OSS_REGION_ID', ''),
        'region'        => env('ALIYUN_OSS_REGION', ''),
        'bucket'        => env('ALIYUN_OSS_BUCKET', ''),
    ]
];
