<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreNotificationBatchRequest extends FormRequest
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
            'channel' => ['required', new Enum(NotificationChannel::class)],
            'type' => ['required', new Enum(NotificationType::class)],
            'message' => ['required', 'string', 'max:1000'],
            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['required', 'integer', 'exists:recipients,id'],
        ];
    }
}
