<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Custom;
use App\Models\Field;
use App\Models\Item;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FieldMutation
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
        $field = Field::where('project_id', $projectId)
            ->find($args['id']);
        if (!$field) {
            throw new GraphQLException("字段不存在");
        }
        $field->delete();
        Item::where('project_id', $projectId)
            ->where('custom_id', $field->custom_id)
            ->update(['content' => DB::raw('JSON_REMOVE(content, "$.' . $field->name . '")')]);
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
            'custom_id' => 'required|integer',
            'type' => 'required',
        ];
        $messages = [
            'name.required' => '请输入表名',
            'name.max' => '表名不能超过255字符',
            'zh_name.required' => '请输入表显示名称',
            'zh_name.max' => '表显示名称不能超过255字符',
            'custom_id.required' => '请输入表ID',
            'custom_id.integer' => '表ID必须是数字类型',
            'type.required' => '请输入字段类型',
        ];
        $validator = Validator::make($args, $rules, $messages);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        $customId = $args['custom_id'];
        $custom = Custom::where('project_id', $projectId)
            ->find($customId);
        if (!$custom) {
            throw new GraphQLException("表不存在");
        }
        $name = $args['name'];
        $zhName = $args['zh_name'];
        $type = $args['type'];
        $originName = '';
        if ($id) {
            $field = Field::where('project_id', $projectId)
                ->where('custom_id', $customId)
                ->find($id);
            if (!$field) {
                throw new GraphQLException("字段不存在");
            }
            $originName = $field->name;
        }
        $query = Field::where('project_id', $projectId)
            ->where('custom_id', $customId)
            ->where('name', $name);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        $fieldFind = $query->first();
        if ($fieldFind) {
            throw new GraphQLException("字段名已存在，请修改");
        }
        if (!isset($field)) {
            $field = new Field();
            $field->project_id = $projectId;
            $field->custom_id = $customId;
            $field->type = $type;
        }
        $field->name = $name;
        $field->zh_name = $zhName;
        $field->description = arrayGet($args, 'description');
        $field->is_required = arrayGet($args, 'is_required') ?? false;
        $field->is_unique = arrayGet($args, 'is_unique') ?? false;
        $field->is_multiple = arrayGet($args, 'is_multiple') ?? false;
        $field->is_hide = arrayGet($args, 'is_hide') ?? false;
        $field->save();
        if ($id) {
            // 更改字段名后更改json中的字段名
            if ($originName != $field->name) {
                Item::where('project_id', $projectId)
                    ->where('custom_id', $field->custom_id)
                    ->update(['content' => \DB::raw('JSON_INSERT(content, "$.' . $field->name . '", content->"$.' . $originName . '")')]);
                Item::where('project_id', $projectId)
                    ->where('custom_id', $field->custom_id)
                    ->update(['content' => \DB::raw('JSON_REMOVE(content, "$.' . $originName . '")')]);
            }

        } else {
            Item::where('project_id', $projectId)
                ->where('custom_id', $field->custom_id)
                ->update(['content' => \DB::raw('JSON_INSERT(`content`, "$.' . $field->name . '", "")' )]);
        }
        $custDir = base_path('graphql/cust');
        if (!file_exists($custDir)) {
            mkdir($custDir);
        }
        $projectDir = $custDir . '/' . $projectId;
        if (!file_exists($projectDir)) {
            mkdir($projectDir);
        }
        // 路由文件
        $content = $this->routeContent($custom);
        $custPath = $projectDir . '/' . $custom->name . '.graphql';
        file_put_contents($custPath, $content);
        // 总入口文件
        $schemaPath = $custDir . '/schema' . $projectId . '.graphql';
        $schemaContent = '
"A datetime string with format `Y-m-d H:i:s`, e.g. `2018-01-01 13:00:00`."
scalar DateTime @scalar(class: "Nuwave\\\\Lighthouse\\\\Schema\\\\Types\\\\Scalars\\\\DateTime")

"A date string with format `Y-m-d`, e.g. `2011-05-23`."
scalar Date @scalar(class: "Nuwave\\\\Lighthouse\\\\Schema\\\\Types\\\\Scalars\\\\Date")

type Query

type Mutation

#import ' . $projectId . '/*.graphql';
        file_put_contents($schemaPath, $schemaContent);
        return $field;
    }

    protected function routeContent($custom)
    {
        $name = ucwords($custom->name);
        $zhName = $custom->zh_name;
        $content = '
extend type Query @middleware(checks: ["api.auth.user.project"]) @namespace (field: "App\\\\GraphQL\\\\Queries\\\\User") {
    "' . $zhName . '列表"
    user' . $name . 's (
        paginator: PaginatorInput,
        more: ' . $name . 'PaginatorInput): [' . $name . '!]! @getlist(resolver: "ItemQuery@index")

    "查看指定' . $zhName . '"
    user' . $name . '(id: Int!): ' . $name . ' @field(resolver: "ItemQuery@show")
}

extend type Mutation @middleware(checks: ["api.auth.user.project"]) @namespace (field: "App\\\\GraphQL\\\\Mutations\\\\User") {
    "新增' . $zhName . '数据"
    userCreate' . $name . '(data: ' . $name . 'Input!): ' . $name . ' @field(resolver: "ItemMutation@create")

    "更新' . $zhName . '数据"
    userUpdate' . $name . '(data: ' . $name . 'Input!): ' . $name . ' @field(resolver: "ItemMutation@update")

    "删除' . $zhName . '数据"
    userDelete' . $name . '(id: Int!): Boolean @field(resolver: "ItemMutation@destroy")
}';
        $fields = Field::where('custom_id', $custom->id)
            ->get();
        $fieldContent = 'id: ID
    "表ID"
    custom_id: Int';
        foreach ($fields as $field) {
            $fieldContent = $fieldContent . '
    "' . $field->zh_name . '"' . '
    ' . $field->name . ': String';
        }
        $content .= '
type ' . $name . ' {
    ' . $fieldContent . '
}

input ' . $name . 'PaginatorInput {
    ' . $fieldContent . '
}

input ' . $name . 'Input {
    ' . $fieldContent . '
}';
        return $content;
    }
}