<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRelocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pipe_inventory_id' => 'required|exists:pipe_inventories,id',
            'target_rack_id' => 'required|exists:warehouse_racks,id',
            'operator_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
