<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiToolCatalogEndpoint extends UuidModel
{
    use SoftDeletes;

    protected $table = 'ai_tool_catalog_endpoints';

    protected $fillable = [
            'ai_tool_id',
            'endpoint_type',
            'value',
            'normalized_value',
            'normalized_value_hash',
            'is_primary',
        ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function aiTool(): BelongsTo { return $this->belongsTo(AiToolCatalog::class, 'ai_tool_id'); }
}

