<?php

namespace App\Http\Middleware\ApiAuth;

use App\Exceptions\MyException;
use App\Models\Project;
use App\Models\Token;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class UserApiProject extends BaseMiddleware
{
    /**
     * @param $request Request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        $projectId = $request->header('Project-Id');
        if (empty($projectId)) {
            throw new \App\Exceptions\GraphQLException("项目不存在");
        }
        $lang = $request->header('lang');
        if ($lang) {
            $request->lang = $lang;
        }
        // 有token传入的判断token是否存在，到控制器里判断有没有具体的权限
        $tokenStr = $request->header('token');
        if ($tokenStr) {
            $project = Project::find($projectId);
            if (!$project) {
                throw new \App\Exceptions\GraphQLException("项目不存在");
            }
            $token = Token::where('token', $tokenStr)
                ->where('project_id', $project->id)
                ->first();
            if (!$token) {
                throw new \App\Exceptions\GraphQLException("token有误");
            }
            $request->this_project_id = $project->id;
            $request->token = $token;
            return $next($request);
        }
        if (\App::environment('local') && $request->header('Debug-Id')) {
            $userId = $request->header('Debug-Id');
            $user = User::where('id', $userId)->first();
            if (!$user) {
                $this->error('user not found', 401);
            }
            auth('user')->login($user);
            $this->checkProject($request, $user);
            return $next($request);
        }

        if (\App::environment('local') && $request->get('uid')) {
            $userId = $request->get('uid');
            $user = User::where('id', $userId)->first();
            if (!$user) {
                $this->error('user not found', 401);
            }
            auth('user')->login($user);
            $this->checkProject($request, $user);
            return $next($request);
        }
        if (! $user = auth('user')->user()) {
            $this->error('user not found', 401);
        }
        $this->checkProject($request, $user);
        return $next($request);
    }

    public function error($message, $code = 401)
    {
        $myException = new MyException();
        $myException->setMessage($message, $code);
        throw $myException;
    }

    protected function checkProject($request, $user)
    {
        $projectId = $request->header('Project-Id');
        if (empty($projectId)) {
            throw new \App\Exceptions\GraphQLException("项目不存在");
        }
        $project = $user->projects
            ->where('id', $projectId)
            ->first();
        if (!$project) {
            throw new \App\Exceptions\GraphQLException("项目不存在");
        }
        $request->this_project_id = $project->id;
    }
}
