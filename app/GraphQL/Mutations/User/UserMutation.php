<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserMutation
{
    public function updatePwd($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $userId = auth('user')->id();
        $user = User::query()
            ->find($userId);
        $validator = Validator::make($args, [
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.required' => '新密码不能为空',
            'password_confirmation.required' => '确认密码不能为空',
            'password.confirmed' => '确认密码不一致',
        ]);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        $oldPassword = array_get($args, 'old_password');
        if (!Hash::check($oldPassword, $user->password)) {
            throw new GraphQLException('原密码不正确');
        }
        $user->password = bcrypt(array_get($args, 'password'));
        $user->save();
        return true;
    }
}
