<?php

namespace App\Models;

class Field extends BaseModel
{
    const TYPE_SINGLE_TEXT = 'single_text';
    const TYPE_MULTI_TEXT = 'multi_text';
    const TYPE_RICH_TEXT = 'rich_text';
    const TYPE_ASSET = 'asset';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function custom()
    {
        return $this->belongsTo(Custom::class);
    }
}
