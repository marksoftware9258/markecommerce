<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function update(User $user, array $data): User
    {
        $allowed = ['name', 'phone', 'timezone', 'locale', 'metadata'];
        $user->update(array_intersect_key($data, array_flip($allowed)));
        return $user->fresh();
    }

    public function uploadAvatar(User $user, UploadedFile $file): User
    {
        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store("avatars/{$user->id}", 'public');
        $user->update(['avatar' => $path]);

        return $user->fresh();
    }

    public function deleteAvatar(User $user): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
    }

    public function changePassword(User $user, array $data): void
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $data['password']]);
        activity('auth')->causedBy($user)->log('Password changed');
    }
}
