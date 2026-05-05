<?php

namespace Modules\Export\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Export\Database\Factories\ExportFactory;
use Modules\User\Models\User;
use Shared\Traits\HasUlid;

class Export extends Model
{
    use HasFactory, HasUlid;

    protected static function newFactory(): ExportFactory
    {
        return ExportFactory::new();
    }

    protected $fillable = [
        'ulid',
        'user_id',
        'module',
        'format',
        'status',
        'path',
        'filename',
        'error',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
