<?php
// ============================================================
// app/Http/Requests/Auth/RegisterRequest.php
// ============================================================
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email|max:200',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20|unique:users,phone',
        ];
    }
}
