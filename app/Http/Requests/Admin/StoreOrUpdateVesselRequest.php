<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateVesselRequest extends FormRequest
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
       $vessel = $this->route('vessel');
        $vesselId = $vessel ? $vessel->id : null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('vessels', 'name')->ignore($vesselId)],
            'imo_number' => ['required', 'string', 'max:50', Rule::unique('vessels', 'imo_number')->ignore($vesselId)],
            'party_id' => ['required', 'exists:parties,id'], // Must map to a valid shipping agent/operator party
            'mmsi_number' => ['nullable', 'string', 'max:50', Rule::unique('vessels', 'mmsi_number')->ignore($vesselId)],
            'call_sign' => ['nullable', 'string', 'max:50', Rule::unique('vessels', 'call_sign')->ignore($vesselId)],
            'flag' => ['nullable', 'string', 'max:100'],
            'vessel_type' => ['nullable', 'string', 'max:100'],
            'gross_tonnage' => ['nullable', 'string', 'max:50'],
            'net_tonnage' => ['nullable', 'string', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.unique' => 'A registered ship or vessel with this name already exists.',
            'imo_number.unique' => 'This exact IMO Number signature is already tracking another asset.',
            'party_id.exists' => 'The selected corporate entity or tracking party is invalid.',
        ];
    }
}
