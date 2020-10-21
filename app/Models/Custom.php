<?php

namespace App\Models;

class Custom extends BaseModel
{
    // 附件表表名
    const TABLE_ASSET_NAME = 'Asset';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    public function translateFields()
    {
        return $this->hasMany(Field::class)->where('is_mult_language', 1);
    }
}
