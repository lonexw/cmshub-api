<?php

namespace App\GraphQL\Queries;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Sts\Sts;
use App\Exceptions\GraphQLException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CommonQuery
{
    public function getAliyunOssSts($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        AlibabaCloud::accessKeyClient(config('aliyun.oss.access_key'), config('aliyun.oss.access_secret'))->regionId(config('aliyun.oss.region_id'))->asDefaultClient();

        $result = Sts::v20150401()
            ->assumeRole()
            //指定角色ARN
            ->withRoleArn(config('aliyun.oss.role_arn'))
            //RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
            ->withRoleSessionName('client_name')
            //设置Policy以进一步限制角色的权限
            //以下权限策略表示拥有所有OSS的只读权限
            //->withPolicy('{
            //     "Statement":[
            //        {
            //             "Action":
            //         [
            //             "oss:Get*",
            //             "oss:List*"
            //             ],
            //              "Effect": "Allow",
            //              "Resource": "*"
            //        }
            //           ],
            //  "Version": "1"
            //}')
            ->connectTimeout(60)
            ->timeout(65)
            ->request();

        if ( ! isset($result['Credentials']['AccessKeyId'])) {
            throw new GraphQLException('获取失败');
        }

        return [
            'region'            => config('aliyun.oss.region'),
            'bucket'            => config('aliyun.oss.bucket'),
            'access_key_id'     => $result['Credentials']['AccessKeyId'],
            'access_key_secret' => $result['Credentials']['AccessKeySecret'],
            'sts_token'         => $result['Credentials']['SecurityToken'],
        ];
    }

}
