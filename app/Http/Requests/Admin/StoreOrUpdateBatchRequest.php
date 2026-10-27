<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateBatchRequest extends FormRequest
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
        $batch = $this->route('batch');
        $batchId = $batch ? $batch->id : null;

         return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('batches', 'name')->ignore($batchId)
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The batch identifier string is required.',
            'name.unique' => 'A tracking batch with this identical name already exists in the logs.',
        ];
    }
}
