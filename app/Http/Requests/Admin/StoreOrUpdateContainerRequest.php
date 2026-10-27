<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateContainerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $container = $this->route('container');
        $containerId = $container ? $container->id : null;

        return [
            'container_number' => ['nullable', 'string', 'max:50', Rule::unique('containers', 'container_number')->ignore($containerId)],
            'container_name' => 'nullable|string|max:255',
            'container_size' => 'required|in:20ft,40ft,40ft HC,45ft',
            'container_type' => 'required|in:SOC,COC',
            'booking_number' => 'nullable|string|max:100',
            'seal_number' => 'nullable|string|max:100',
            'voyage_id' => 'required|exists:voyages,id',
            'batch_id' => 'required|exists:batches,id',
            'vgm' => 'nullable|numeric|min:0|max:999999.99',
            'tare_weight' => 'nullable|numeric|min:0',
            'max_gross_weight' => 'nullable|numeric|min:0',
            'total_pkg' => 'nullable|integer|min:0',
            'status' => 'required|in:empty,loaded,in_transit,discharged',

            // Relational Type Enforcement Guards
            'transitor_id' => [
                'required',
                Rule::exists('parties', 'id')->where('type', 'transitor')
            ],
            'shipper_id' => [
                'required',
                Rule::exists('parties', 'id')->where('type', 'shipper')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'container_number.unique' => 'A cargo container with this tracking registration code already exists.',
            'transitor_id.exists' => 'The selected entity is invalid or is not classified as a Transitor.',
            'shipper_id.exists' => 'The selected entity is invalid or is not classified as a Shipper.',
        ];
    }
}
