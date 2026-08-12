<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutboundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:150',
            'destination' => 'required|string|max:255',
            'truck_number' => 'required|string|max:30',
            'driver_name' => 'required|string|max:100',
            'bundle_ids' => 'required|array|min:1',
            'bundle_ids.*' => 'exists:pipe_inventories,id',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
