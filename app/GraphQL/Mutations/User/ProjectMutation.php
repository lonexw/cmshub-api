<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Custom;
use App\Models\Field;
use App\Models\Project;
use App\Services\SchemaService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ProjectMutation
{
    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $args = $args['data'];
        $user = auth('user')->user();
        $name = arrayGet($args, 'name');
        $project = Project::where('name', $name)
            ->where('user_id', $user->id)
            ->first();
        if ($project) {
            throw new GraphQLException("项目名称不能重复");
        }
        $project = new Project();
        $project->name = $name;
        $project->user_id = $user->id;
        $project->description = arrayGet($args, 'description') ?? '';
        $project->save();
        $custom = new Custom();
        $custom->project_id = $project->id;
        $custom->name = 'asset';
        $custom->zh_name = '附件表';
        $custom->description = '存放图片、文件、视频等';
        $custom->save();

        $field = new Field();
        $field->project_id = $project->id;
        $field->custom_id = $custom->id;
        $field->type = Field::TYPE_SINGLE_TEXT;
        $field->name = 'name';
        $field->zh_name = '名称';
        $field->description = '资源名称';
        $field->is_required = false;
        $field->is_unique = false;
        $field->is_multiple = false;
        $field->is_hide = false;
        $field->save();

        $field = new Field();
        $field->project_id = $project->id;
        $field->custom_id = $custom->id;
        $field->type = Field::TYPE_SINGLE_TEXT;
        $field->name = 'url';
        $field->zh_name = '地址';
        $field->description = '资源地址';
        $field->is_required = false;
        $field->is_unique = true;
        $field->is_multiple = false;
        $field->is_hide = false;
        $field->save();

        $field = new Field();
        $field->project_id = $project->id;
        $field->custom_id = $custom->id;
        $field->type = Field::TYPE_SINGLE_TEXT;
        $field->name = 'is_system';
        $field->zh_name = '是否系统';
        $field->description = '增加文件url还是上传文件';
        $field->is_required = false;
        $field->is_unique = true;
        $field->is_multiple = false;
        $field->is_hide = false;
        $field->save();

        $field = new Field();
        $field->project_id = $project->id;
        $field->custom_id = $custom->id;
        $field->type = Field::TYPE_SINGLE_TEXT;
        $field->name = 'type';
        $field->zh_name = '文件类型';
        $field->description = '图片/视频/文件，记录文件类型';
        $field->is_required = false;
        $field->is_unique = true;
        $field->is_multiple = false;
        $field->is_hide = false;
        $field->save();

        $field = new Field();
        $field->project_id = $project->id;
        $field->custom_id = $custom->id;
        $field->type = Field::TYPE_SINGLE_TEXT;
        $field->name = 'file_size';
        $field->zh_name = '文件大小';
        $field->description = '便于之后统计文件容量';
        $field->is_required = false;
        $field->is_unique = true;
        $field->is_multiple = false;
        $field->is_hide = false;
        $field->save();

        $schemaService = new SchemaService();
        $schemaService->generateRoute($custom);
        return $project;
    }

    public function update($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $args = $args['data'];
        $user = auth('user')->user();
        $name = arrayGet($args, 'name');
        $id = arrayGet($args, 'id');
        if (!$id) {
            throw new GraphQLException("请传入ID");
        }
        $projectFind = Project::find($id);
        if (!$projectFind) {
            throw new GraphQLException("项目不存在");
        }
        $project = Project::where('name', $name)
            ->where('user_id', $user->id)
            ->where('id', '<>', $id)
            ->first();
        if ($project) {
            throw new GraphQLException("项目名称不能重复");
        }
        $projectFind->name = $name;
        $projectFind->description = arrayGet($args, 'description') ?? '';
        $projectFind->save();
        return $projectFind;
    }

    public function destroy($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = auth('user')->user();
        $project = Project::where('user_id', $user->id)
            ->find($args['id']);
        if (!$project) {
            throw new GraphQLException('项目不存在');
        }
        $schemaService = new SchemaService();
        $schemaService->deleteProjectRoute($project);
        $project->delete();
        return true;
    }
}
