<?php

namespace App\Models;

class Custom extends BaseModel
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
