<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->filled('company_id') && $this->filled('contact_id')) {
                    $belongs = Contact::where('id', $this->input('contact_id'))
                        ->where('company_id', $this->input('company_id'))
                        ->exists();

                    if (! $belongs) {
                        $validator->errors()->add(
                            'contact_id',
                            'The selected contact does not belong to the selected company.'
                        );
                    }
                }
            },
        ];
    }
}
