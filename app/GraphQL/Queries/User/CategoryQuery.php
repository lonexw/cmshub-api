<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseQuery;
use App\Models\Category;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CategoryQuery extends BaseQuery
{
    protected function wheres()
    {
        return [
            function (Builder $q) {
                $projectId = $this->getInputArgs('this_project_id');
                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
            },
        ];
    }

    protected function order()
    {
        return [new Expression('id desc')];
    }

    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $customs = Category::getList($this->getConditions($args), ['customs']);
        return $customs;
    }

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $project = Category::query()
            ->where('project_id', $projectId)
            ->find($args['id']);
        return $project;
    }
}
