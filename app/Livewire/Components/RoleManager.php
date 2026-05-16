<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Role;
use App\Models\RoleAssignment;

class RoleManager extends Component
{
    public $authType;

    public $authId;

    public $open = false;

    public $roles = [];

    public $selectedRoles = [];

    public function mount($authType, $authId)
    {
        $this->authType = $authType;

        $this->authId = $authId;

        $this->loadRoles();
    }

    public function loadRoles()
    {
        $this->roles = Role::orderBy('name')
            ->get();

        $this->selectedRoles = RoleAssignment::where(
                'auth_type',
                $this->authType
            )
            ->where(
                'auth_id',
                $this->authId
            )
            ->pluck('role_id')
            ->toArray();
    }

    public function toggle()
    {
        $this->open = !$this->open;
    }

    public function save()
    {
        RoleAssignment::where(
            'auth_type',
            $this->authType
        )
        ->where(
            'auth_id',
            $this->authId
        )
        ->delete();

        foreach ($this->selectedRoles as $roleId) {

            RoleAssignment::create([
                'auth_type' => $this->authType,
                'auth_id'   => $this->authId,
                'role_id'   => $roleId,
            ]);
        }

        session()->flash(
            'message',
            'Roles updated successfully.'
        );
    }

    public function render()
    {
        return view('livewire.components.role-manager');
    }
}