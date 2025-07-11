<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Sender information
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:20'],
            'sender_address' => ['required', 'string', 'max:500'],
            'sender_postal_code' => ['required', 'string', 'max:10'],
            'sender_area_id' => ['nullable', 'string', 'max:50'],
            'sender_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'sender_longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Receiver information
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:20'],
            'receiver_address' => ['required', 'string', 'max:500'],
            'receiver_postal_code' => ['required', 'string', 'max:10'],
            'receiver_area_id' => ['nullable', 'string', 'max:50'],
            'receiver_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'receiver_longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Package information
            'package_type' => ['nullable', 'string', 'in:package,document'],
            'package_weight' => ['required', 'integer', 'min:1', 'max:50000'],
            'package_length' => ['nullable', 'integer', 'min:1', 'max:200'],
            'package_width' => ['nullable', 'integer', 'min:1', 'max:200'],
            'package_height' => ['nullable', 'integer', 'min:1', 'max:200'],
            'package_description' => ['nullable', 'string', 'max:500'],
            'package_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],

            // Courier selection
            'courier_code' => ['required', 'string', 'max:50'],
            'courier_service' => ['required', 'string', 'max:100'],

            // Pricing
            'shipping_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'insurance_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'total_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],

            // Additional options
            'notes' => ['nullable', 'string', 'max:1000'],
            'auto_create_biteship_order' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sender_name' => 'sender name',
            'sender_phone' => 'sender phone',
            'sender_address' => 'sender address',
            'sender_postal_code' => 'sender postal code',
            'receiver_name' => 'receiver name',
            'receiver_phone' => 'receiver phone',
            'receiver_address' => 'receiver address',
            'receiver_postal_code' => 'receiver postal code',
            'package_weight' => 'package weight',
            'package_description' => 'package description',
            'courier_code' => 'courier',
            'courier_service' => 'courier service',
            'shipping_cost' => 'shipping cost',
            'total_cost' => 'total cost',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'package_weight.min' => 'Package weight must be at least 1 gram.',
            'package_weight.max' => 'Package weight cannot exceed 50 kg (50,000 grams).',
            'package_type.in' => 'Package type must be either package or document.',
        ];
    }
}
