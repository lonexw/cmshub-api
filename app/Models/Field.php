<?php

namespace App\Models;

class Field extends BaseModel
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function custom()
    {
        return $this->belongsTo(Custom::class);
    }
}
