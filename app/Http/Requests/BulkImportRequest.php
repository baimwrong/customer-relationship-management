<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isSalesManager();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'string', 'in:leads,contacts,companies,deals'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // 10MB max
        ];
    }
}
