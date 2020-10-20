<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseQuery;;

use App\Models\ItemTranslate;
use App\Models\Language;
use App\Models\ProjectLanguage;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class LanguageQuery extends BaseQuery
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

    public function allIndex($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $languages = Language::getList($this->getConditions($args));
        return $languages;
    }

    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $projectLanguages = ProjectLanguage::getList($this->getConditions($args));
        return $projectLanguages;
    }

    public function getCheckCode($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $id = $args['id'];
        $code = '';
        $itemTranslate = ItemTranslate::where('item_id', $id)->first();
        if ($itemTranslate) {
            $code = $itemTranslate->code;
        }
        return $code;
    }
}