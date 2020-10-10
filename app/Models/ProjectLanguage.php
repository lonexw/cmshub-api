<?php

namespace App\Models;

class ProjectLanguage extends BaseModel
{
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

}
