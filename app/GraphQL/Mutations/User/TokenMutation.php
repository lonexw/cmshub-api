<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Token;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class TokenMutation
{
    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args);
    }

    public function update($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args);
    }

    public function destroy($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $token = Token::where('project_id', $projectId)
            ->find($args['id']);
        if (!$token) {
            throw new GraphQLException('数据不存在');
        }
        $token->customs()->detach();
        $token->customs()->delete();
        $token->delete();
        return true;
    }

    public function store($args)
    {
        $projectId = $args['this_project_id'];
        $args = $args['data'];
        $id = arrayGet($args, 'id');
        $rules = [
            'token' => 'required|max:255',
            'custom_ids' => 'required|array',
            'scopes' => 'required|array',
        ];
        $messages = [
            'token.required' => '请输入token',
            'token.max' => 'token不能超过255字符',
            'custom_ids.required' => '请输入表名',
            'custom_ids.array' => '表名是数组格式',
            'scopes.required' => '请输入权限名',
            'scopes.array' => '权限名是数组格式',
        ];
        $validator = Validator::make($args, $rules, $messages);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        $customIds = arrayGet($args, 'custom_ids');
        $scopes = arrayGet($args, 'scopes');
        if ($id) {
            $token = Token::where('project_id', $projectId)
                ->find($id);
            if (!$token) {
                throw new GraphQLException("数据不存在");
            }
        }
        $tokenStr = arrayGet($args, 'token');
        $query = Token::where('project_id', $projectId)
            ->where('token', $tokenStr);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        $tokenFind = $query->first();
        if ($tokenFind) {
            throw new GraphQLException("token已存在，请修改");
        }
        if (!isset($token)) {
            $token = new Token();
            $token->project_id = $projectId;
        }
        $token->token = $tokenStr;
        $token->custom_ids = $customIds;
        $token->scopes = $scopes;
        $token->description = arrayGet($args, 'description');
        $token->save();
        $token->customs()->sync($customIds);
        return $token;
    }
}
