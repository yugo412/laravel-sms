<?php

namespace Yugo\SMSGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'number',
        'image_path',
    ];
}
