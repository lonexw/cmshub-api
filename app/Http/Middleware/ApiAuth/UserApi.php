<?php

namespace App\Http\Middleware\ApiAuth;

use App\Exceptions\MyException;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class UserApi extends BaseMiddleware
{
    /**
     * @param $request Request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (\App::environment('local') && $request->header('Debug-Id')) {
            $userId = $request->header('Debug-Id');
            $user = User::where('id', $userId)->first();
            if (!$user) {
                $this->error('user not found', 401);
            }
            auth('user')->login($user);

            return $next($request);
        }

        if (\App::environment('local') && $request->get('uid')) {
            $userId = $request->get('uid');
            $user = User::where('id', $userId)->first();
            if (!$user) {
                $this->error('user not found', 401);
            }
            auth('user')->login($user);

            return $next($request);
        }

        if (! $user = auth('user')->user()) {
            $this->error('user not found', 401);
        }
        return $next($request);
    }

    public function error($message, $code = 401) {
        $myException = new MyException();
        $myException->setMessage($message, $code);
        throw $myException;
    }
}
