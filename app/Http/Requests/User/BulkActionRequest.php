<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'action'  => 'required|in:delete,activate,deactivate,suspend',
            'ids'     => 'required|array|min:1',
            'ids.*'   => 'integer|exists:users,id',
        ];
    }
}
