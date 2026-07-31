<?php

namespace App\Http\Requests;

use App\Models\Domain;
use App\Services\BidService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOfferRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            'amount' => ['required', 'integer', 'min:'.(int) config('bids.minimum_amount')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $domain = $this->attributes->get('domain');

            if (! $domain instanceof Domain) {
                return;
            }

            $highest = app(BidService::class)->highestAcceptedAmount($domain);

            if ((int) $this->input('amount') <= $highest) {
                $validator->errors()->add('amount', 'Bid must be higher than the current highest bid.');
            }
        });
    }
}
