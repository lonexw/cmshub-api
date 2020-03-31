<?php


namespace App\GraphQL\Queries\User;


use App\Models\Project;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ProjectQuery
{
    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = auth('user')->user();
        return $user->projects;
    }

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $project = Project::find($projectId);
        return $project;
    }
}