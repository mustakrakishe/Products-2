<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email|max:255|exists:users,email',
            'password' => 'required|string|max:255|confirmed',
        ];
    }

    protected function getRedirectUrl()
    {
        if ($this->expectsJson()) {
            return parent::getRedirectUrl();
        }

        if ($this->validator->errors()->has('token')) {
            return route('password.reset.send');
        }

        return parent::getRedirectUrl();
    }
}
