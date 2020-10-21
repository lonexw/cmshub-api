<?php


namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseQuery;
use App\Models\Custom;
use App\Models\Field;
use App\Models\ItemTranslate;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ItemTranslateQuery extends BaseQuery
{

    protected function wheres()
    {
        $wheres = [];
        $wheres[] = function (Builder $q) {
            $projectId = $this->getInputArgs('this_project_id');
            if ($projectId) {
                $q->where('project_id', $projectId);
            }
            $customId = $this->getInputArgs('custom_id');
            if ($customId) {
                $q->where('custom_id', $customId);
            }
            $status = $this->getInputArgs('status', null);
            if (isset($status)) {
                $q->where('status', $status);
            }
            $ids = $this->getInputArgs('ids', []);
            if (isset($ids) && count($ids) > 0) {
                $q->whereIn('id', $ids);
            }
            $id = $this->getInputArgs('id');
            if ($id) {
                $q->where('id', $id);
            }
            $beginAt = $this->getInputArgs('begin_at', null);
            if (isset($beginAt)) {
                $q->where('created_at', '>=', $beginAt);
            }
            $endAt = $this->getInputArgs('end_at', null);
            if (isset($endAt)) {
                $q->where('created_at', '<=', $endAt);
            }
            $lang = $this->getInputArgs('lang');
            if ($lang) {
                $q->where('code', $lang);
            }
        };
        $other = function (Builder $q) {
            $args = $this->getInputArgs();
            $thisFields = $args['this_fields'];
            foreach ($args as $arg => $value) {
                if ($arg != 'this_project_id' && $arg != 'custom_id' && $arg != 'status'
                    && $arg != 'ids' && $arg != 'id' && $arg != 'begin_at'
                    && $arg != 'end_at' && $arg != 'directive' && $arg != 'this_fields'
                    && $arg != 'paginator') {
                    $isId = false;
                    $needle = 'Ids';
                    $temp = explode($needle, $arg);
                    if(count($temp) > 1){
                        $field = $temp[0];
                    } else {
                        $field = $arg;
                    }
                    if ($thisFields && $item = $thisFields->where('name', $field)->first()) {
                        if ($item->type == Field::TYPE_ASSET || $item->type == Field::TYPE_REFERENCE) {
                            $isId = true;
                        }
                    }
                    if ($isId) {
                        if (count($temp) > 1) {
                            $q->whereIn('content->' . $field, $value);
                        } else {
                            $q->where('content->' . $field, $value);
                        }
                    } else {
                        // 如果是要查询字段
                        $q->where('content->' . $field, 'like', '%' . $value . '%');
                    }
                }
            }
        };
        $wheres[] = $other;
        return $wheres;
    }

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $id = $args['id'];
        $code = $args['code'];
        $content = '';
        $itemTranslate = ItemTranslate::where('item_id', $id)->where('code', $code)->first();
        if ($itemTranslate) {
            $content = $itemTranslate->content;
        }
        return $content;
    }
}