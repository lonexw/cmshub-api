<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Field;
use App\Models\Item;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ItemMutation
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
        $item = Item::where('project_id', $projectId)
            ->find($args['id']);
        if (!$item) {
            throw new GraphQLException("数据不存在");
        }
        $item->delete();
        return true;
    }

    public function store($args)
    {
        $projectId = $args['this_project_id'];
        $args = $args['data'];
        $id = arrayGet($args, 'id');
        $customId = $args['custom_id'];
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

        $item->content = $content;
        $item->save();
        foreach ($content as $field => $value) {
            $item[$field] = $value;
        }
        unset($item->content);
        return $item;
    }
}
