<?php

namespace App\Models;

class Setting extends BaseModel
{
    protected $fillable = ['key', 'value', 'type', 'group'];
}
