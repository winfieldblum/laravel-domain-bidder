<?php

namespace App\Models;

use Database\Factories\DomainFeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $domain_id
 * @property string $icon
 * @property string $title
 * @property string $description
 * @property string|null $color
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'domain_id',
    'icon',
    'title',
    'description',
    'color',
    'sort_order',
])]
class DomainFeature extends Model
{
    /** @use HasFactory<DomainFeatureFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Domain, $this>
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
