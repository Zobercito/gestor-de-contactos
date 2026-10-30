<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public bool $showForm = false;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'viewer';

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function create(): void
    {
        Gate::authorize('create', User::class);

        $this->reset('name', 'email', 'password', 'role');
        $this->role = UserRole::Viewer->value;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('create', User::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => mb_strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ])->forceFill(['email_verified_at' => now()])->save();

        $this->showForm = false;
        $this->reset('name', 'email', 'password');
        Flux::toast(text: 'Usuario creado.', variant: 'success');
    }

    public function updateRole(int $userId, string $role): void
    {
        $user = User::query()->findOrFail($userId);
        Gate::authorize('update', $user);

        if ($user->id === auth()->id()) {
            Flux::toast(text: 'No puedes cambiar tu propio rol.', variant: 'danger');

            return;
        }

        if (! in_array($role, array_column(UserRole::cases(), 'value'), true)) {
            Flux::toast(text: 'Rol no válido.', variant: 'danger');

            return;
        }

        if ($user->isAdmin() && User::query()->where('role', UserRole::Admin)->count() <= 1) {
            Flux::toast(text: 'Debe quedar al menos una persona administradora.', variant: 'danger');

            return;
        }

        $user->update(['role' => $role]);
        Flux::toast(text: 'Rol actualizado.', variant: 'success');
    }

    public function delete(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Gate::authorize('delete', $user);

        $user->delete();
        Flux::toast(text: 'Usuario eliminado.', variant: 'success');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ])->layout('components.layouts.app');
    }
}
