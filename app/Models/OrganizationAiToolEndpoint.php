<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrganizationAiToolEndpoint extends UuidModel
{
    use SoftDeletes;

    protected $table = 'organization_ai_tool_endpoints';

    protected $fillable = [
            'organization_id',
            'organization_ai_tool_id',
            'endpoint_type',
            'value',
            'normalized_value',
            'normalized_value_hash',
            'match_mode',
        ];

    protected function casts(): array
    {
        return [];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class, 'organization_id'); }

    public function organizationAiTool(): BelongsTo { return $this->belongsTo(OrganizationAiTool::class, 'organization_ai_tool_id'); }
}

