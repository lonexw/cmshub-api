<?php


namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseQuery;
use App\Models\Field;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FieldQuery extends BaseQuery
{
    protected function wheres()
    {
        return [
            function (Builder $q) {
                $projectId = $this->getInputArgs('this_project_id');
                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
                $customId = $this->getInputArgs('custom_id');
                if ($customId) {
                    $q->where('custom_id', $customId);
                }
                $isMultLanguage = $this->getInputArgs('is_mult_language');
                if ($isMultLanguage) {
                    $q->where('is_mult_language', $isMultLanguage);
                }
            },
        ];
    }

    protected function order()
    {
        return ['-id'];
    }

    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $fields = Field::getList($this->getConditions($args), ['project', 'custom']);
        return $fields;
    }

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $filed = Field::where('project_id', $projectId)
            ->find($args['id']);
        return $filed;
    }

    public function translateIndex($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $args['is_mult_language'] = 1;
        $translateFields = Field::getList($this->getConditions($args), ['project', 'custom']);
        return $translateFields;
    }
}
