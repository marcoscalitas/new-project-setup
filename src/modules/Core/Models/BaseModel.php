<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasUlid;

abstract class BaseModel extends Model
{
    use HasFactory, HasUlid, SoftDeletes;
}
