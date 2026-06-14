<?php

namespace App\Http\Requests;

use App\Enums\ProviderDeliveryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderDeliveryStatusRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'provider_message_id' => ['required', 'string'],
            'status' => [
                'required',
                Rule::in(ProviderDeliveryStatus::values()),
            ],
            'failure_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
