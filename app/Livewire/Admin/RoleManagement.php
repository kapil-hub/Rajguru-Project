<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Helpers\MenuHelper;

class RoleManagement extends Component
{
    public $roles = [];

    public $name;

    public $slug;

    public $description;

    public $search = '';

    public $editId = null;
    public $routes = null;

    public $showModal = false;

    protected $rules = [

        'name' => 'required|min:2',

        'slug' => 'required',

        'description' => 'nullable'

    ];

    public function updatedName()
    {
        if (!$this->editId) {

            $this->slug = Str::slug($this->name);

        }
    }

    public function openModal()
    {
        $this->resetForm();

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        Role::updateOrCreate(

            ['id' => $this->editId],

            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
            ]

        );

        session()->flash(
            'success',
            $this->editId
                ? 'Role updated successfully.'
                : 'Role created successfully.'
        );

        $this->closeModal();

        $this->resetForm();
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $this->editId = $role->id;

        $this->name = $role->name;

        $this->slug = $role->slug;

        $this->description = $role->description;

        $this->showModal = true;
    }

    public function delete($id)
    {
        Role::findOrFail($id)->delete();

        session()->flash(
            'success',
            'Role deleted successfully.'
        );
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'slug',
            'description',
            'editId'
        ]);
    }

    public function render()
    {
        $this->roles = Role::where(function ($q) {

                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');

            })
            ->latest()
            ->get();
        $this->routes = MenuHelper::getMenuGroups();
     
        return view(
            'livewire.admin.role-management'
        );
    }
}