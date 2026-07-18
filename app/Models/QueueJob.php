<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QueueJob extends Model
{
    use HasFactory;

    protected $table = 'jobs';

    protected $fillable = [
            'queue',
            'payload',
            'attempts',
            'reserved_at',
            'available_at',
        ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'attempts' => 'integer',
            'reserved_at' => 'datetime',
            'available_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}

