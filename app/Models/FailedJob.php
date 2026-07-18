<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FailedJob extends UuidModel
{
    protected $table = 'failed_jobs';

    protected $fillable = [
            'uuid',
            'connection',
            'queue',
            'payload',
            'exception',
            'failed_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
        ];
    }
}

