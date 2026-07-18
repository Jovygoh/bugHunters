<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiToolCatalog extends UuidModel
{
    use SoftDeletes;

    protected $table = 'ai_tool_catalog';

    protected $fillable = [
            'vendor_id',
            'name',
            'slug',
            'description',
            'category',
            'delivery_model',
            'default_risk_level',
            'stores_prompts',
            'trains_on_customer_data',
            'supports_enterprise_controls',
            'data_residency',
            'security_attributes',
            'metadata',
        ];

    protected function casts(): array
    {
        return [
            'stores_prompts' => 'boolean',
            'trains_on_customer_data' => 'boolean',
            'supports_enterprise_controls' => 'boolean',
            'data_residency' => 'array',
            'security_attributes' => 'array',
            'metadata' => 'array',
        ];
    }

    public function vendor(): BelongsTo { return $this->belongsTo(AiToolVendor::class, 'vendor_id'); }

    public function endpoints(): HasMany { return $this->hasMany(AiToolCatalogEndpoint::class, 'ai_tool_id'); }

    public function organizationProfiles(): HasMany { return $this->hasMany(OrganizationAiTool::class, 'catalog_ai_tool_id'); }
}

