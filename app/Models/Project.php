<?php

namespace App\Models;

class Project extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customs()
    {
        return $this->hasMany(Custom::class);
    }
}
