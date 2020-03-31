<?php


namespace App\GraphQL\Queries\User;


use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserQuery
{
    public function me($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = auth('user')->user();
        return $user;
    }

    public function logout($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        auth('user')->logout();
        return "退出成功";
    }
}