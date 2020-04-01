<?php

namespace App\GraphQL\Queries\User;

use App\Exceptions\GraphQLException;
use App\Jobs\EmailJob;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Cache;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthQuery
{
    const CAPTCHA_CACHE_PREFIX     = "email_code:";
    const VERIFY_CODE_CACHE_PREFIX = "email:user:verify:";
    const CAPTCHA_CACHE_TTL        = 300;
    const VERIFY_CODE_CACHE_TTL    = 300;

    public function getEmailCode($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $email = $args['email'];
        if (!$email) {
            throw new GraphQLException('请输入邮箱');
        }
        if (cache(self::VERIFY_CODE_CACHE_PREFIX . $email)) {
            throw new GraphQLException('请稍后重试');
        }
        try {
            $code = makePassword();
            EmailJob::dispatch($code, $email, '注册验证码');
            cache()->put(self::VERIFY_CODE_CACHE_PREFIX . $email, $code, self::VERIFY_CODE_CACHE_TTL);
            return true;
        } catch (\Exception $exception) {
            throw new GraphQLException("发送验证码异常", ["exception_message" => $exception->getMessage()]);
        }
        return true;
    }

    public function register($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $email = $args['email'];
        $code = $args['code'];
        $password = $args['password'];
        $user = User::where('email', $email)->first();
        if ($user) {
            throw new GraphQLException('此邮箱已注册');
        }
        $cacheCode = cache(self::VERIFY_CODE_CACHE_PREFIX . $email);
        if (!$cacheCode) {
            throw new GraphQLException('请获取验证码');
        }
        if ($code != $cacheCode) {
            throw new GraphQLException('验证码不正确');
        }
        $user = new User();
        $user->name = '';
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->save();
        Cache::forget(self::VERIFY_CODE_CACHE_PREFIX . $email);
        return true;
    }

    public function login($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $token = auth('user')->attempt(['email' => $args['email'], 'password' => $args['password']]);
        if (!$token) {
            throw new GraphQLException("邮箱或密码错误");
        }
        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('user')->factory()->getTTL() * 60,
        ];
    }
}
