<?php


namespace App\GraphQL\Queries\User;

use App\Exceptions\GraphQLException;
use App\GraphQL\BaseQuery;
use App\Models\Custom;
use App\Models\Field;
use App\Models\Item;
use App\Models\Token;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ItemQuery extends BaseQuery
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

    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $userPluralName = $resolveInfo->fieldName;
        $pluralName = substr($userPluralName, 4);
        $custom = Custom::with('fields.referenceCustom')
            ->where('project_id', $projectId)
            ->where('plural_name', $pluralName)
            ->first();
        if (!$custom) {
            throw new GraphQLException("表结构不存在");
        }
        $this->hasPermission($context, $custom);
        $fields = $custom->fields;
        $args['custom_id'] = $custom->id;
        $args['this_project_id'] = $projectId;
        $args['this_fields'] = $custom->fields;
        $items = Item::getList($this->getConditions($args));
        $asset = Custom::where('project_id', $projectId)
            ->where('name', 'asset')
            ->first();
        foreach ($items as $item) {
            $content = $item->content;
            foreach ($content as $field => $value) {
                $item[$field] = $value;
                $this->withModel($fields, $field, $item, $asset);
            }
            unset($item->content);
        }
        return $items;
    }

    function withModel($fields, $field, &$item, $asset)
    {
        $content = $item->content;
        $assetField = $fields->where('type', Field::TYPE_ASSET)
            ->where('name', $field)
            ->first();
        // 判断是否附件，需要返回关联的附件表信息
        if ($assetField) {
            if ($assetField->is_multiple) {
                $modelAll = [];
                if ($asset) {
                    $assetItems = Item::where('custom_id', $asset->id)
                        ->whereIn('id', $content[$assetField->name])
                        ->get();
                    foreach ($assetItems as $assetItem) {
                        if ($assetItem && $assetItem->content) {
                            foreach ($assetItem->content as $fieldItem => $valueItem) {
                                $assetItem[$fieldItem] = $valueItem;
                            }
                            $modelAll[] = $assetItem;
                        }
                    }
                }
            } else {
                $modelAll = null;
                if ($asset) {
                    $assetItem = Item::where('custom_id', $asset->id)
                        ->where('id', $content[$assetField->name])
                        ->first();
                    if ($assetItem && $assetItem->content) {
                        foreach ($assetItem->content as $fieldItem => $valueItem) {
                            $assetItem[$fieldItem] = $valueItem;
                        }
                        $modelAll = $assetItem;
                    }
                }
            }
            $item[$assetField->name . Item::NAME_ASSET] = $modelAll;
        }
        // 判断是否为关联模型类型，是的需要返回对应模型
        $referenceField = $fields->where('type', Field::TYPE_REFERENCE)
            ->where('name', $field)
            ->first();
        if ($referenceField) {
            if ($referenceField->is_multiple) {
                $modelAll = [];
                $referenceModels = Item::where('custom_id', $referenceField->reference_custom_id)
                    ->whereIn('id', $content[$referenceField->name])
                    ->get();
                foreach ($referenceModels as $referenceModel) {
                    if ($referenceModel && $referenceModel->content) {
                        foreach ($referenceModel->content as $fieldItem => $valueItem) {
                            $referenceModel[$fieldItem] = $valueItem;
                        }
                        $modelAll[] = $referenceModel;
                    }
                }
            } else {
                $modelAll = null;
                $referenceModel = Item::where('custom_id', $referenceField->reference_custom_id)
                    ->where('id', $content[$referenceField->name])
                    ->first();
                if ($referenceModel && $referenceModel->content) {
                    foreach ($referenceModel->content as $fieldItem => $valueItem) {
                        $referenceModel[$fieldItem] = $valueItem;
                    }
                    $modelAll = $referenceModel;
                }
            }

            $item[$referenceField->name . Item::NAME_REFERENCE] = $modelAll;
        }
    }

    public function show($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $userName = $resolveInfo->fieldName;
        $name = substr($userName, 4);
        $custom = Custom::with('fields')
            ->where('project_id', $projectId)
            ->where('name', $name)
            ->first();
        if (!$custom) {
            throw new GraphQLException("表结构不存在");
        }
        $this->hasPermission($context, $custom);
        $fields = $custom->fields;

        $item = Item::where('project_id', $projectId)
            ->find($args['id']);
        $asset = Custom::where('project_id', $projectId)
            ->where('name', 'asset')
            ->first();
        $content = $item->content;
        foreach ($content as $field => $value) {
            $item[$field] = $value;
            // 返回关联附件、模型
            $this->withModel($fields, $field, $item, $asset);
        }
        unset($item->content);
        return $item;
    }

    function hasPermission($context, $custom)
    {
        $token = $context->request->token;
        if (!$token) {
            return;
        }
        $scopes = $token->scopes;
        if (!(in_array(Token::SCOPE_OPEN, $scopes) || in_array(Token::SCOPE_QUERY, $scopes))) {
            throw new GraphQLException("无此权限");
        }
        $customIds = $token->custom_ids;
        if (!in_array($custom->id, $customIds)) {
            throw new GraphQLException("无此权限");
        }
    }
}