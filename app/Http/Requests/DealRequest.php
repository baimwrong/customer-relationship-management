<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;

class DealRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'stage' => ['required', 'string', 'in:'.implode(',', array_keys(Deal::STAGES))],
            'amount' => ['required', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'probability' => ['nullable', 'integer', 'between:0,100'],
            'deal_type' => ['required', 'string', 'in:new_business,existing_business,renewal'],
            'loss_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
