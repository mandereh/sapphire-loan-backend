<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $eighteenYearsAgo = today()->subYears(18)->toDateString();
        return [
            'title' => 'required|string|max:30',
            'bvn' => 'nullable|digits:11',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone_number' => 'required|digits:11|unique:users',
            'gender' => 'required|in:male,female',
            'date_of_birth' => ['required','date', "before:$eighteenYearsAgo"],
            'ippis_number' => 'required|numeric',
        ];
    }
}
