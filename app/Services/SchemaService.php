<?php

namespace App\Services;

use App\Models\Field;

class SchemaService
{
    public function generateRoute($custom)
    {
        $projectId = $custom->project_id;
        $custDir = base_path('graphql/cust');
        if (!file_exists($custDir)) {
            mkdir($custDir);
        }
        $projectDir = $custDir . '/' . $projectId;
        if (!file_exists($projectDir)) {
            mkdir($projectDir);
        }
        // 路由文件
        $content = $this->routeContent($custom);
        $custPath = $projectDir . '/' . $custom->name . '.graphql';
        file_put_contents($custPath, $content);
        // 总入口文件
        $schemaPath = $custDir . '/schema' . $projectId . '.graphql';
        $schemaContent = '
"A datetime string with format `Y-m-d H:i:s`, e.g. `2018-01-01 13:00:00`."
scalar DateTime @scalar(class: "Nuwave\\\\Lighthouse\\\\Schema\\\\Types\\\\Scalars\\\\DateTime")

"A date string with format `Y-m-d`, e.g. `2011-05-23`."
scalar Date @scalar(class: "Nuwave\\\\Lighthouse\\\\Schema\\\\Types\\\\Scalars\\\\Date")

type Query

type Mutation

#import ' . $projectId . '/*.graphql';
        file_put_contents($schemaPath, $schemaContent);
    }

    public function deleteCustomRoute($custom)
    {
        $projectId = $custom->project_id;
        $custDir = base_path('graphql/cust');
        $projectDir = $custDir . '/' . $projectId;
        $custPath = $projectDir . '/' . $custom->name . '.graphql';
        if (file_exists($custPath)) {
            unlink($custDir);
        }
    }

    /**
     * 删除目录及文件
     * @param $dirName
     */
    function delDirAndFile( $dirName )
    {
        if ($handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        $this->delDirAndFile("$dirName/$item");
                    } else {
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
            rmdir($dirName);
        }
    }

    public function deleteProjectRoute($project)
    {
        $projectId = $project->id;
        $custDir = base_path('graphql/cust');
        $projectDir = $custDir . '/' . $projectId;
        if (file_exists($projectDir)) {
            $this->delDirAndFile($projectDir);
        }
        $schemaPath = $custDir . '/schema' . $projectId . '.graphql';
        if (file_exists($schemaPath)) {
            unlink($schemaPath);
        }
    }

    protected function routeContent($custom)
    {
        $name = ucwords($custom->name);
        $zhName = $custom->zh_name;
        $content = '
extend type Query @middleware(checks: ["api.auth.user.project"]) @namespace (field: "App\\\\GraphQL\\\\Queries\\\\User") {
    "' . $zhName . '列表"
    user' . $name . 's (
        paginator: PaginatorInput,
        more: ' . $name . 'PaginatorInput): [' . $name . '!]! @getlist(resolver: "ItemQuery@index")

    "查看指定' . $zhName . '"
    user' . $name . '(id: Int!): ' . $name . ' @field(resolver: "ItemQuery@show")
}

extend type Mutation @middleware(checks: ["api.auth.user.project"]) @namespace (field: "App\\\\GraphQL\\\\Mutations\\\\User") {
    "新增' . $zhName . '数据"
    userCreate' . $name . '(data: ' . $name . 'Input!): ' . $name . ' @field(resolver: "ItemMutation@create")

    "更新' . $zhName . '数据"
    userUpdate' . $name . '(data: ' . $name . 'Input!): ' . $name . ' @field(resolver: "ItemMutation@update")

    "删除' . $zhName . '数据"
    userDelete' . $name . '(id: Int!): Boolean @field(resolver: "ItemMutation@destroy")
}';
        $fields = Field::where('custom_id', $custom->id)
            ->get();
        $fieldContent = 'id: ID
    "表ID"
    custom_id: Int';
        $assetsField = '';
        foreach ($fields as $field) {
            $fieldContent = $fieldContent . '
    "' . $field->zh_name . '"' . '
    ' . $field->name . ': String';

            if ($field->type == Field::TYPE_ASSET) {
                $assetsField = '
    "' . $field->zh_name . '对应附件模型"';
                // 附件单独处理
                if ($field->is_multiple) {
                    $assetsField .= '
    ' . $field->name . 'Asset: [Asset]';
                } else {
                    $assetsField .= '
    ' . $field->name . 'Asset: Asset';
                }
            }
        }
        $fieldContent = $fieldContent . '
    "状态 0 草稿 1 发布"' . '
    status: String';

        $typeContent = '
type ' . $name . ' {';

        if ($assetsField) {
            $typeContent .= $assetsField;
        }

        $typeContent .= '
    ' . $fieldContent . '
}';

        $content .= $typeContent;
        $content .= '
input ' . $name . 'PaginatorInput {
    ' . $fieldContent . '
}

input ' . $name . 'Input {
    ' . $fieldContent . '
}';
        return $content;
    }
}
