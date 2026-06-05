<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min($filters['per_page'] ?? 15, 100);
        $sort    = $filters['sort'] ?? 'created_at';
        $order   = $filters['order'] ?? 'desc';

        return User::with(['roles'])
            ->filter($filters)
            ->orderBy($sort, $order)
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'phone'    => $data['phone'] ?? null,
            'status'   => $data['status'] ?? UserStatus::Active,
            'timezone' => $data['timezone'] ?? 'UTC',
            'locale'   => $data['locale'] ?? 'en',
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        } else {
            $user->assignRole('user');
        }

        if (!empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        activity('user-management')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("User '{$user->email}' created");

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $updateData = array_filter([
            'name'     => $data['name'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'status'   => $data['status'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'locale'   => $data['locale'] ?? null,
        ], fn ($v) => !is_null($v));

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $updateData['email'] = $data['email'];
            $updateData['email_verified_at'] = null;
        }

        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        $user->update($updateData);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return $user->fresh(['roles', 'permissions']);
    }

    public function delete(User $user): void
    {
        // Revoke all tokens on delete
        $user->tokens()->delete();
        $user->delete();

        activity('user-management')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("User '{$user->email}' deleted");
    }

    public function updateStatus(User $user, string $status): User
    {
        $user->update(['status' => $status]);

        if ($status === 'suspended') {
            $user->tokens()->delete();
        }

        activity('user-management')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("User status changed to '{$status}'");

        return $user;
    }

    public function bulkAction(string $action, array $ids): array
    {
        // Prevent acting on own account
        $ids = array_filter($ids, fn ($id) => $id !== auth()->id());

        $users = User::whereIn('id', $ids)->get();

        $processed = 0;
        foreach ($users as $user) {
            match ($action) {
                'delete'    => $this->delete($user),
                'activate'  => $this->updateStatus($user, 'active'),
                'suspend'   => $this->updateStatus($user, 'suspended'),
                'deactivate'=> $this->updateStatus($user, 'inactive'),
                default     => null,
            };
            $processed++;
        }

        return ['processed' => $processed, 'total' => count($ids)];
    }
}
