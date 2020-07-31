<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Category;
use App\Models\Custom;
use App\Models\Field;
use App\Services\SchemaService;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CategoryMutation
{
    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args);
    }

    public function update($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args);
    }

    public function store($args)
    {
        $projectId = $args['this_project_id'];
        $args = $args['data'];
        $id = arrayGet($args, 'id');
        $rules = [
            'title' => 'required|max:255'
        ];
        $messages = [
            'title.required' => '请输入名称'
        ];
        $validator = Validator::make($args, $rules, $messages);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        if ($id) {
            $category = Category::query()
                ->where('project_id', $projectId)
                ->find($id);
            if (!$category) {
                throw new GraphQLException("分类不存在");
            }
        }
        $title = array_get($args, 'title');
        $query = Category::query()
            ->where('project_id', $projectId)
            ->where('title', $title);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        $categoryFind = $query->first();
        if ($categoryFind) {
            throw new GraphQLException("分类名称已存在，请修改");
        }
        if (!isset($category)) {
            $category = new Category();
            $category->project_id = $projectId;
        }
        $category->title = $title;
        $category->save();
        return $category;
    }

    public function updateSequence($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        // 项目分类
        $categories = Category::query()
            ->where('project_id', $projectId)
            ->get();
        // 排序数据
        $data = collect($args['data']);
        foreach ($categories as $category) {
            $item = $data->where('id', $category->id)
                ->first();
            if ($item) {
                $category->sequence = $item['sequence'];
                $category->save();
            }
        }
        return true;
    }
}
