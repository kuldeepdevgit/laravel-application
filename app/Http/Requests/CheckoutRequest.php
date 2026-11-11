<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'address' => ['required', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:80'],
            'region' => ['required', 'string', 'max:80'],
            'postal' => ['required', 'string', 'max:20'],
            'country' => ['required', 'in:India,United States,Canada,United Kingdom'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pay' => ['required', 'in:cash'],
        ];
    }
}
