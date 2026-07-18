<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JobBatch extends Model
{
    use HasFactory;

    protected $table = 'job_batches';

    protected $fillable = [
            'name',
            'total_jobs',
            'pending_jobs',
            'failed_jobs',
            'failed_job_ids',
            'options',
            'cancelled_at',
            'finished_at',
        ];

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'total_jobs' => 'integer',
            'pending_jobs' => 'integer',
            'failed_jobs' => 'integer',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}

