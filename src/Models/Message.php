<?php

namespace Yugo\SMSGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class Message extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'message_id',
        'contact_id',
        'user_id',
        'source',
        'destination',
        'text',
        'metadata',
        'status',
    ];

    protected $casts = [
        'message_id' => 'integer',
        'contact_id' => 'integer',
        'user_id' => 'integer',
        'metadata' => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
