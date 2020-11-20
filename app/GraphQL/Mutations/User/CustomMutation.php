<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Custom;
use App\Models\Field;
use App\Services\SchemaService;
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
            throw new GraphQLException('表不存在');
        }
        if ($custom->name == 'asset') {
            throw new GraphQLException('系统表，无法删除');
        }
        $schemaService = new SchemaService();
        $schemaService->deleteCustomRoute($custom);
        Field::where('custom_id', $custom->id)->delete();
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
            'plural_name' => 'required|max:255',
            'zh_name' => 'required|max:255',
        ];
        $messages = [
            'name.required' => '请输入表名',
            'name.max' => '表名不能超过255字符',
            'plural_name.required' => '请输入api复数名称',
            'plural_name.max' => 'api复数名称不能超过255字符',
            'zh_name.required' => '请输入表显示名称',
            'zh_name.max' => '表显示名称不能超过255字符',
        ];
        $validator = Validator::make($args, $rules, $messages);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        $name = $args['name'];
        $zhName = $args['zh_name'];
        $pluralName = arrayGet($args, 'plural_name');
        if (strtolower($name) == strtolower($pluralName)) {
            throw new GraphQLException("表名和api复数名不能重复");
        }
        if ($id) {
            $custom = Custom::where('project_id', $projectId)
                ->find($id);
            if (!$custom) {
                throw new GraphQLException("表不存在");
            }
            $schemaService = new SchemaService();
            $schemaService->deleteCustomRoute($custom);
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
        $custom->category_id = array_get($args, 'category_id');
        $custom->name = $name;
        $custom->plural_name = $pluralName;
        $custom->zh_name = $zhName;
        $custom->description = arrayGet($args, 'description');
        $custom->save();
        // 修改表结构文件
        $schemaService = new SchemaService();
        $schemaService->generateRoute($custom);
        return $custom;
    }
}
