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

    protected function rules()
    {
        return [
            'name' => 'required|min:2',
            'slug' => 'required|alpha_dash|unique:roles,slug,' . ($this->editId ?: 'NULL') . ',id',
            'description' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'name.required' => 'Role name is required.',
        'name.min' => 'Role name must be at least 2 characters.',
        'slug.required' => 'Slug is required.',
        'slug.alpha_dash' => 'Slug may only contain letters, numbers, dashes and underscores.',
        'slug.unique' => 'This slug is already used by another role.',
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
        $this->slug = Str::slug((string) $this->slug);

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
