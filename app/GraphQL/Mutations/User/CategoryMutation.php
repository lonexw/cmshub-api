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
        $title = array_get($args, 'title');
        $query = Category::query()
            ->where('project_id', $projectId)
            ->where('title', $title);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        $categoryFind = $query->first();
        if ($categoryFind) {
            throw new GraphQLException("表名名称已存在，请修改");
        }
        if (!isset($categoryFind)) {
            $category = new Category();
            $category->project_id = $projectId;
        }
        $category->title = $title;
        $category->save();
        return $category;
    }
}
