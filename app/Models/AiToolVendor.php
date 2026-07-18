<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiToolVendor extends UuidModel
{
    use SoftDeletes;

    protected $table = 'ai_tool_vendors';

    protected $fillable = [
            'name',
            'normalized_name',
            'website_url',
            'privacy_url',
            'terms_url',
            'country_code',
            'metadata',
        ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function catalogTools(): HasMany { return $this->hasMany(AiToolCatalog::class, 'vendor_id'); }
}

