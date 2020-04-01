<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatrixLab\LaravelAdvancedSearch\AdvancedSearchTrait;
use MatrixLab\LaravelAdvancedSearch\WithAndSelectForGraphQLGeneratorTrait;

class BaseModel extends Model
{
    use AdvancedSearchTrait, WithAndSelectForGraphQLGeneratorTrait;
}
