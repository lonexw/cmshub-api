<?php

namespace App\Models;

class Token extends BaseModel
{
    protected $casts = [
        'custom_ids' => 'array',
        'scopes' => 'array'
    ];

    // 权限 query 查询/mutation 增删改/open 开放
    const SCOPE_QUERY = 'query';
    const SCOPE_MUTATION = 'mutation';
    const SCOPE_OPEN = 'open';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customs()
    {
        return $this->belongsToMany(Custom::class);
    }
}
