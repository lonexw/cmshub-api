<?php

namespace App\Models;

class Item extends BaseModel
{
    protected $casts = [
        'content' => 'array'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function custom()
    {
        return $this->belongsTo(Custom::class);
    }
}
