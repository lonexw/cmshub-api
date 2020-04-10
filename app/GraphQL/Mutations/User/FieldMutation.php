<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Custom;
use App\Models\Field;
use App\Models\Item;
use App\Services\SchemaService;
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
        $field = Field::with('custom')
            ->where('project_id', $projectId)
            ->find($args['id']);
        if (!$field) {
            throw new GraphQLException("字段不存在");
        }
        // 如果是模型关联字段删除关联的表字段
        if ($field->type == Field::TYPE_REFERENCE) {
            if ($field->is_main) {
                // 主表的要找到关联表，删除关联表字段
                $mainField = Field::where('reference_field_id', $field->id)->first();
                if ($mainField) {
                    // 删除字段对应的内容
                    Item::where('project_id', $projectId)
                        ->where('custom_id', $mainField->custom_id)
                        ->update(['content' => DB::raw('JSON_REMOVE(content, "$.' . $mainField->name . '")')]);
                    $mainField->delete();
                }
            } else {
                // 关联表要找到主表
                $oppositeField = Field::find($field->reference_field_id);
                if ($oppositeField) {
                    // 删除字段对应的内容
                    Item::where('project_id', $projectId)
                        ->where('custom_id', $oppositeField->custom_id)
                        ->update(['content' => DB::raw('JSON_REMOVE(content, "$.' . $oppositeField->name . '")')]);
                    $oppositeField->delete();
                }
            }
        }
        // 删除字段
        $field->delete();
        // 删除字段对应的内容
        Item::where('project_id', $projectId)
            ->where('custom_id', $field->custom_id)
            ->update(['content' => DB::raw('JSON_REMOVE(content, "$.' . $field->name . '")')]);
        $schemaService = new SchemaService();
        $schemaService->generateRoute($field->custom);
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
            'name.required' => '请输入字段名',
            'name.max' => '字段名不能超过255字符',
            'zh_name.required' => '请输入字段显示名称',
            'zh_name.max' => '字段显示名称不能超过255字符',
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
        $systemFields = ['id', 'status', 'created_at', 'updated_at', 'custom_id', 'project_id',
            'content', 'asset'];
        $name = $args['name'];
        if (in_array(strtolower($name), $systemFields)) {
            throw new GraphQLException('此字段是系统字段，请更改字段名');
        }
        $zhName = $args['zh_name'];
        $type = $args['type'];
        $referenceCustomId = arrayGet($args, 'reference_custom_id');
        $referenceField = arrayGet($args, 'reference_field');
        if ($type == Field::TYPE_REFERENCE) {
            // 关联模型类型的字段需要判断是否输入反向关联的数据
            if (!$referenceCustomId) {
                throw new GraphQLException('请输入关联表ID');
            }
            $referenceCustom = Custom::where('project_id', $projectId)
                ->find($referenceCustomId);
            if (!$referenceCustom) {
                throw new GraphQLException('关联表不存在');
            }
            if (!$referenceField) {
                throw new GraphQLException('请输入反向关联字段信息');
            }
            $rules = [
                'name' => 'required|max:255',
                'zh_name' => 'required|max:255',
            ];
            $messages = [
                'name.required' => '请输入反向关联字段名',
                'name.max' => '反向关联字段名不能超过255字符',
                'zh_name.required' => '请输入反向关联字段显示名称',
                'zh_name.max' => '反向关联字段显示名称不能超过255字符',
            ];
            $validator = Validator::make($referenceField, $rules, $messages);
            if ($validator->fails()) {
                throw new GraphQLException($validator->errors()->first());
            }
            $referenceFieldFind = Field::where('name', arrayGet($referenceField, 'name'))
                ->where('custom_id', $referenceCustomId)
                ->first();
            if ($referenceFieldFind) {
                throw new GraphQLException('反向关联字段名称已存在');
            }
        }
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
        if ($type == Field::TYPE_REFERENCE && !$id) {
            // 模型关联类型要保存关联的表ID
            $field->is_main = Field::IS_MAIN_YES;
            $field->reference_custom_id = $referenceCustomId;
            $field->save();
            // 模型关联要保存反向关联的字段信息
            $fieldReference = new Field();
            $field->is_main = Field::IS_MAIN_NO;
            $fieldReference->project_id = $field->project_id;
            $fieldReference->custom_id = $referenceCustomId;
            $fieldReference->reference_custom_id = $field->custom_id;
            $fieldReference->reference_field_id = $field->id;
            $fieldReference->type = $field->type;
            $fieldReference->name = arrayGet($referenceField, 'name');
            $fieldReference->zh_name = arrayGet($referenceField, 'zh_name');
            $fieldReference->description = arrayGet($referenceField, 'description');
            $fieldReference->is_required = false;
            $fieldReference->is_unique = false;
            $fieldReference->is_multiple = arrayGet($referenceField, 'is_multiple') ?? false;
            $fieldReference->is_hide = false;
            $fieldReference->save();
        } else {
            $field->save();
        }
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
        $schemaService = new SchemaService();
        $schemaService->generateRoute($custom);
        return $field;
    }


}
