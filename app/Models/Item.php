<?php

namespace App\Models;

class Item extends BaseModel
{
    protected $casts = [
        'content' => 'array'
    ];

    // 状态 0 草稿 1 发布
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISH = 1;

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function custom()
    {
        return $this->belongsTo(Custom::class);
    }
}
