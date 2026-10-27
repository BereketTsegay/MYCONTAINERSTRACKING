<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateVoyageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vessel_id' => 'required|exists:vessels,id',
            'voyage_number' => 'nullable|string|max:100',
            'batch_id' => 'nullable|exists:batches,id',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date|after_or_equal:departure_date',
            'origin_port' => 'nullable|string|max:255',
            'destination_port' => 'nullable|string|max:255',
            'party_id' => [
                'nullable',
                // Enforce that the managing party must be a valid shipping line row entity
                Rule::exists('parties', 'id')->where(function ($query) {
                    $query->where('type', 'shipping_line');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vessel_id.required' => 'A valid ship selection is required to track voyages.',
            'party_id.exists' => 'The assigned line agent must be a registered party with a corporate classification type of Shipping Line.',
            'arrival_date.after_or_equal' => 'The arrival timestamp cannot occur prior to the departure schedule parameters.',
        ];
    }
}
