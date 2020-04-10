<?php

namespace App\Models;

class Field extends BaseModel
{
    // 单行文本
    const TYPE_SINGLE_TEXT = 'single_text';
    // 多行文本
    const TYPE_MULTI_TEXT = 'multi_text';
    // 富文本
    const TYPE_RICH_TEXT = 'rich_text';
    // 附件
    const TYPE_ASSET = 'asset';
    // 关联模型
    const TYPE_REFERENCE = 'reference';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function custom()
    {
        return $this->belongsTo(Custom::class);
    }
}
