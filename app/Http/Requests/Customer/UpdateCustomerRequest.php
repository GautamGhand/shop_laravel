<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', Rule::unique('users')->ignore($this->route('customer'))],
            'password' => ['sometimes', 'string', 'min:8'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:500'],
        ];
    }
}
