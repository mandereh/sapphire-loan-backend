<?php

namespace App\Http\Requests;

use App\Rules\RunningLoan;
use Illuminate\Foundation\Http\FormRequest;

class ApplyRequest extends FormRequest
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
        return [
            'loan_type' => 'required|numeric|exists:loan_types,id',
            'product_id' => 'required_if:loan_type,2',
            'organization' => 'required',
            'amount' => 'required|numeric',
            'address' => 'required|string|max:255',
            'city' => 'required|string',
            'zipcode' => 'nullable',
            'account_number' => 'required|digits:10',
            'bank_code' => 'required|numeric',
            'state' => 'required|numeric|exists:states,id',
            'reffered_by' => 'nullable|numeric|exists:users,refferal_code',
            'user_id' => ['required','numeric','exists:users,id', new RunningLoan()],
            'tenor' => 'required|numeric|min:1'
        ];
    }
}
