<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInboundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pipe_product_id' => 'required|exists:pipe_products,id',
            'warehouse_rack_id' => 'required|exists:warehouse_racks,id',
            'heat_number' => 'required|string|max:50',
            'mill_source' => 'required|string|max:100',
            'qty_bundles' => 'required|integer|min:1',
            'qc_status' => 'required|in:PASSED,PENDING,REJECTED',
            'operator_name' => 'nullable|string|max:100',
        ];
    }
}
