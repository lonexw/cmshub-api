<?php


namespace App\GraphQL\Queries\User;

use App\Exceptions\GraphQLException;
use App\GraphQL\BaseQuery;
use App\Models\Custom;
use App\Models\Field;
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
                $status = $this->getInputArgs('status', Item::STATUS_PUBLISH);
                if (isset($status)) {
                    $q->where('status', $status);
                }
            },
        ];
    }

    public function index($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $userPluralName = $resolveInfo->fieldName;
        $pluralName = substr($userPluralName, 4);
        $custom = Custom::with('fields')
            ->where('project_id', $projectId)
            ->where('plural_name', $pluralName)
            ->first();
        if (!$custom) {
            throw new GraphQLException("表结构不存在");
        }
        $fields = $custom->fields;
        $args['this_project_id'] = $projectId;
        $items = Item::getList($this->getConditions($args));
        $asset = Custom::where('project_id', $projectId)
            ->where('name', 'asset')
            ->first();
        foreach ($items as $item) {
            $assetField = $fields->where('type', Field::TYPE_ASSET)->first();
            $content = $item->content;
            foreach ($content as $field => $value) {
                $item[$field] = $value;
            }
            // 判断是否附件，需要返回关联的附件表信息
            if ($assetField) {
                $assetModel = null;
                if ($asset) {
                    $assetItem = Item::where('custom_id', $asset->id)
                        ->where('id', $content[$assetField->name])
                        ->first();
                    if ($assetItem && $assetItem->content) {
                        foreach ($assetItem->content as $fieldItem => $valueItem) {
                            $assetItem[$fieldItem] = $valueItem;
                        }
                        $assetModel = $assetItem;
                    }
                }
                $item[$assetField->name . 'Asset'] = $assetModel;
            }
            unset($item->content);
        }
        return $items;
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
        $fields = $custom->fields;

        $item = Item::where('project_id', $projectId)
            ->find($args['id']);
        $asset = Custom::where('project_id', $projectId)
            ->where('name', 'asset')
            ->first();
        $assetField = $fields->where('type', Field::TYPE_ASSET)->first();
        // 判断是否附件，需要返回关联的附件表信息
        if ($assetField) {
            $assetModel = null;
            if ($asset) {
                $assetItem = Item::where('custom_id', $asset->id)
                    ->where('id', $item->content[$assetField->name])
                    ->first();
                foreach ($assetItem->content as $fieldItem => $valueItem) {
                    $assetItem[$fieldItem] = $valueItem;
                }
                $assetModel = $assetItem;
            }
            $item[$assetField->name . 'Asset'] = $assetModel;
        }
        $content = $item->content;
        foreach ($content as $field => $value) {
            $item[$field] = $value;
        }
        unset($item->content);
        return $item;
    }
}