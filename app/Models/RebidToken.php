<?php

namespace App\Models;

use Database\Factories\RebidTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $domain_id
 * @property int $triggered_by_bid_id
 * @property string $name
 * @property string $email
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'domain_id',
    'triggered_by_bid_id',
    'name',
    'email',
    'token',
    'expires_at',
    'used_at',
])]
class RebidToken extends Model
{
    /** @use HasFactory<RebidTokenFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Domain, $this>
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * @return BelongsTo<Bid, $this>
     */
    public function triggeredByBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'triggered_by_bid_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * @param  Builder<RebidToken>  $query
     * @return Builder<RebidToken>
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }
}
