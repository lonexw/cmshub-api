<?php

namespace App\Models;

class Category extends BaseModel
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customs()
    {
        return $this->hasMany(Custom::class);
    }
}
