<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product ? $product->id : null;

        return [
            // Multi-Category Context Validation Guards
            'pk_category_id' => ['nullable', Rule::exists('categories', 'id')->where('type', 'pklist')],
            'hbill_category_id' => ['nullable', Rule::exists('categories', 'id')->where('type', 'house_bill')],
            'edpass_category_id' => ['nullable', Rule::exists('categories', 'id')->where('type', 'edpass')],

            'name' => 'required|string|max:255',
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => 'nullable|string',
            'hs_code' => 'nullable|string|max:12',
            'has_variations' => 'required|boolean',
            
            // Nested Array Structural Validation Rules for Variations
            'variations' => 'required_if:has_variations,true|array',
            'variations.*.id' => 'nullable|integer',
            'variations.*.unit_of_measurement_id' => 'required|exists:units_of_measurement,id',
            'variations.*.qty_per_unit' => 'required|numeric|min:0.01',
            'variations.*.variation_sku' => 'nullable|string|max:100',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.weight' => 'nullable|numeric|min:0',
            'variations.*.length' => 'nullable|numeric|min:0',
            'variations.*.width' => 'nullable|numeric|min:0',
            'variations.*.height' => 'nullable|numeric|min:0',
            'variations.*.variation_hs_code' => 'nullable|string|max:12',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This product parent SKU signature must be completely unique across active catalogs.',
            'variations.*.qty_per_unit.required' => 'You must define the exact quantity configuration per unit tier.',
        ];
    }
}
