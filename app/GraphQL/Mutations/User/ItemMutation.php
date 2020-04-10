<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Custom;
use App\Models\Field;
use App\Models\Item;
use App\Models\Token;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ItemMutation
{
    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args, $resolveInfo, $context);
    }

    public function update($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args, $resolveInfo, $context);
    }

    public function destroy($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $userName = $resolveInfo->fieldName;
        $name = substr($userName, 10);
        $custom = Custom::with('fields')
            ->where('project_id', $projectId)
            ->where('name', $name)
            ->first();
        if (!$custom) {
            throw new GraphQLException("表结构不存在");
        }
        $this->hasPermission($context, $custom);
        // 根据路由名查询当前操作的哪张表，根据接口权限判断是否可以使用此接口
        $item = Item::where('project_id', $projectId)
            ->find($args['id']);
        if (!$item) {
            throw new GraphQLException("数据不存在");
        }
        $item->delete();
        return true;
    }

    function hasPermission($context, $custom)
    {
        $token = $context->request->token;
        if (!$token) {
            return;
        }
        $scopes = $token->scopes;
        if (!(in_array(Token::SCOPE_OPEN, $scopes) || in_array(Token::SCOPE_MUTATION, $scopes))) {
            throw new GraphQLException("无此权限");
        }
        $customIds = $token->custom_ids;
        if (!in_array($custom->id, $customIds)) {
            throw new GraphQLException("无此权限");
        }
    }

    public function store($args, $resolveInfo, $context)
    {
        $projectId = $args['this_project_id'];
        $args = $args['data'];
        $id = arrayGet($args, 'id');
        $userName = $resolveInfo->fieldName;
        $name = substr($userName, 10);
        $custom = Custom::with('fields')
            ->where('project_id', $projectId)
            ->where('name', $name)
            ->first();
        if (!$custom) {
            throw new GraphQLException("表结构不存在");
        }
        $this->hasPermission($context, $custom);
        $customId = $custom->id;
        if ($id) {
            $item = Item::where('project_id', $projectId)
                ->find($id);
            if (!$item) {
                throw new GraphQLException("数据不存在");
            }
        }
        if (!isset($item)) {
            $item = new Item();
            $item->project_id = $projectId;
            $item->custom_id = $customId;
        }
        $fields = Field::where('custom_id', $customId)
            ->get();
        $content = [];
        foreach ($fields as $field) {
            $fieldValue = arrayGet($args, $field->name) ?? '';
            if ($field->is_required && !$fieldValue) {
                throw new GraphQLException('请输入' . $field->zh_name);
            }
            if ($field->is_unique) {
                if (!$fieldValue) {
                    throw new GraphQLException('请输入' . $field->zh_name);
                }
                $itemQuery = Item::where('content->' . $field->name, $fieldValue)
                    ->where('project_id', $projectId)
                    ->where('custom_id', $customId);
                if ($id) {
                    $itemQuery->where('id', '<>', $id);
                }
                $itemFind = $itemQuery->first();
                if ($itemFind) {
                    if (!($id && $item->content[$field->name] == $fieldValue)) {
                        throw new GraphQLException($field->zh_name . '已存在，请修改');
                    }
                }
            }
            $content[$field->name] = $fieldValue;
        }
        $status = arrayGet($args, 'status') ?? 0;
        $item->status = $status;
        $item->content = $content;
        $item->save();
        foreach ($content as $field => $value) {
            $item[$field] = $value;
        }
        unset($item->content);
        return $item;
    }
}
