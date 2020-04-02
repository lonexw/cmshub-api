<?php


namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseQuery;
use App\Models\Item;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ItemQuery extends BaseQuery
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
            },
        ];
    }

    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $items = Item::getList($this->getConditions($args), ['project', 'custom']);
        foreach ($items as $item) {
            $content = $item->content;
            foreach ($content as $field => $value) {
                $item[$field] = $value;
            }
        }
        return $items;
    }

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $item = Item::where('project_id', $projectId)
            ->find($args['id']);
        return $item->content;
    }
}