<?php

namespace App\GraphQL\Mutations\User;

use App\Exceptions\GraphQLException;
use App\Models\Language;
use App\Models\ProjectLanguage;
use App\Models\Token;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Validator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class LanguageMutation
{
    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        return $this->store($args);
    }


    public function destroy($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $projectId = $context->request->this_project_id;
        $args['this_project_id'] = $projectId;
        $language = ProjectLanguage::where('project_id', $projectId)
            ->find($args['id']);
        if (!$language) {
            throw new GraphQLException('数据不存在');
        }
        $language->delete();
        return true;
    }

    public function store($args)
    {
        $projectId = $args['this_project_id'];
        $args = $args['data'];
        $rules = [
            'language_id' => 'required',
        ];
        $messages = [
            'language_id.required' => '请选择语言',
        ];
        $validator = Validator::make($args, $rules, $messages);
        if ($validator->fails()) {
            throw new GraphQLException($validator->errors()->first());
        }
        $languageId = arrayGet($args, 'language_id');
        $cLanguage = Language::where('id', $languageId)->first();
        if ($cLanguage && $cLanguage->code == 'CN') {
            throw new GraphQLException("中文是默认语言，无需添加");
        }
        $languageFind = ProjectLanguage::where('project_id', $projectId)
            ->where('language_id', $languageId)->first();
        if ($languageFind) {
            throw new GraphQLException("该语言已存在");
        }
        $language = new ProjectLanguage();
        $language->project_id = $projectId;
        $language->language_id = $languageId;
        $language->code = $cLanguage->code;
        $language->save();
        return $language;
    }
}
