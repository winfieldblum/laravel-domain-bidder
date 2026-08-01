<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $hostname
 * @property string $display_name
 * @property string $tagline
 * @property string $description
 * @property bool $is_active
 * @property string $mail_from_address
 * @property string $mail_from_name
 * @property string|null $notification_email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'hostname',
    'display_name',
    'tagline',
    'description',
    'is_active',
    'mail_from_address',
    'mail_from_name',
    'notification_email',
])]
class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<DomainFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(DomainFeature::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<DomainSellingPoint, $this>
     */
    public function sellingPoints(): HasMany
    {
        return $this->hasMany(DomainSellingPoint::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<Bid, $this>
     */
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * @return HasMany<DomainImpression, $this>
     */
    public function impressions(): HasMany
    {
        return $this->hasMany(DomainImpression::class);
    }

    /**
     * @param  Builder<Domain>  $query
     * @return Builder<Domain>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function notificationRecipient(): string
    {
        return $this->notification_email ?: (string) config('bids.notification_email');
    }
}
