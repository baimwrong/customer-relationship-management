<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'create_company' => ['nullable', 'boolean'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'new_company_name' => ['nullable', 'string', 'max:255'],
            'create_deal' => ['nullable', 'boolean'],
            'deal_title' => ['nullable', 'string', 'max:255'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'deal_stage' => ['nullable', 'string', 'in:'.implode(',', array_keys(Deal::STAGES))],
            'expected_close_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
