<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailRequest extends EmailVerificationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! hash_equals(
            sha1(User::find($this->id)->getEmailForVerification()),
            (string) $this->route('hash')
        )) {
            return false;
        }

        return true;
    }
}
