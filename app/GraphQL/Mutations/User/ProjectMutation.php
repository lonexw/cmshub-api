<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\AdmissionInformation;
use App\Models\Kindergarten;
use App\Models\Project;
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
}
