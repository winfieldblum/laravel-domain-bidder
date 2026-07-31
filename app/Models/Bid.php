<?php

namespace App\Models;

use App\Enums\BidStatus;
use Database\Factories\BidFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $domain_id
 * @property string $name
 * @property string $email
 * @property int $amount
 * @property BidStatus $status
 * @property Carbon|null $email_verified_at
 * @property string|null $verification_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'domain_id',
    'name',
    'email',
    'amount',
    'status',
    'email_verified_at',
    'verification_token',
])]
class Bid extends Model
{
    /** @use HasFactory<BidFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Domain, $this>
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => BidStatus::class,
            'email_verified_at' => 'datetime',
        ];
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * @param  Builder<Bid>  $query
     * @return Builder<Bid>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * @param  Builder<Bid>  $query
     * @return Builder<Bid>
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', BidStatus::Accepted);
    }
}
