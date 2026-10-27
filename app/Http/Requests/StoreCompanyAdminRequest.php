<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyAdminRequest extends FormRequest
{
    /**
     * Check whether the logged-in user can create a Company Admin.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Validate Company Admin form data.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id')
                    ->where('status', 'active'),
            ],
        ];
    }
}
