<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(Activity::TYPES))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'activity_date' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:pending,completed,cancelled'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'deal_id' => ['nullable', 'exists:deals,id'],
        ];
    }
}
