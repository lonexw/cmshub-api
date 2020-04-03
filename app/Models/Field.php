<?php

namespace App\Models;

class Field extends BaseModel
{
    const TYPE_SINGLE_TEXT = 'single_text';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function custom()
    {
        return $this->belongsTo(Custom::class);
    }
}
