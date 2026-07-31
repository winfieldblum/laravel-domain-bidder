<?php

namespace App\Http\Requests;

use App\Models\RebidToken;
use App\Services\BidService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVerifiedOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'amount' => ['required', 'integer', 'min:'.(int) config('bids.minimum_amount')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var \App\Models\Domain|null $resolvedDomain */
            $resolvedDomain = $this->attributes->get('domain');

            $token = RebidToken::query()
                ->where('token', $this->route('token'))
                ->first();

            if (
                $token === null
                || ! $token->isValid()
                || $resolvedDomain === null
                || $token->domain_id !== $resolvedDomain->id
            ) {
                $validator->errors()->add('token', 'This rebid link is invalid or has expired.');

                return;
            }

            $highest = app(BidService::class)->highestAcceptedAmount($resolvedDomain);

            if ((int) $this->input('amount') <= $highest) {
                $validator->errors()->add('amount', 'Bid must be higher than the current highest bid.');
            }
        });
    }
}
