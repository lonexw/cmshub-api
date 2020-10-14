<?php


namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseQuery;
use App\Models\ItemTranslate;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ItemTranslateQuery extends BaseQuery
{

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $id = $args['id'];
        $content = '';
        $itemTranslate = ItemTranslate::where('item_id', $id)->first();
        if ($itemTranslate) {
            $content = $itemTranslate->content;
        }
        return $content;
    }
}