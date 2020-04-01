<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Custom;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CustomMutation
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
        $custom = Custom::where('project_id', $projectId)
            ->find($args['id']);
        if (!$custom) {
            throw new GraphQLException("表不存在");
        }
        $custom->delete();
        return true;
    }

    public function store($args)
    {
        $projectId = $args['this_project_id'];
        $args = $args['data'];
        $id = arrayGet($args, 'id');
        $rules = [
            'name' => 'required|max:255',
            'zh_name' => 'required|max:255',
        ];
        $messages = [
            'name.required' => '请输入表名',
            'name.max' => '表名不能超过255字符',
            'zh_name.required' => '请输入表显示名称',
            'zh_name.max' => '表显示名称不能超过255字符',
        ];
        $validator = Validator::make($args, $rules, $messages);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        $name = $args['name'];
        $zhName = $args['zh_name'];
        if ($id) {
            $custom = Custom::where('project_id', $projectId)
                ->find($id);
            if (!$custom) {
                throw new GraphQLException("表不存在");
            }
        }
        $query = Custom::where('project_id', $projectId)
            ->where('name', $name);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        $customFind = $query->first();
        if ($customFind) {
            throw new GraphQLException("表名已存在，请修改");
        }
        if (!isset($custom)) {
            $custom = new Custom();
            $custom->project_id = $projectId;
        }
        $custom->name = $name;
        $custom->zh_name = $zhName;
        $custom->description = arrayGet($args, 'description');
        $custom->save();
        return $custom;
    }
}
