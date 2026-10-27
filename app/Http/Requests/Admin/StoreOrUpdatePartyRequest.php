<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdatePartyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $party = $this->route('party');
        $partyId = $party ? $party->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // 2. Apply unique checking constraint, passing the ignored record ID
                Rule::unique('parties', 'name')->ignore($partyId),
            ],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('parties', 'email')->ignore($partyId)],
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('parties', 'phone')->ignore($partyId)],
            'address' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:customer,shipping_line,transitor,shipper'],
        ];
    }
}
