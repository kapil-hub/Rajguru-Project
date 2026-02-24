<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Courses as Course;
use App\Models\Departments as Department;

class CourseManager extends Component
{
    use WithPagination;

    public $name;
    public $dept_id;
    public $program_code;
    public $types;
    public $status = 1;

    public $courseId;
    public $isOpen = false;

    public $departments = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'dept_id' => 'required|exists:departments,id',
        'program_code' => 'nullable|string|max:100',
        'types' => 'required|integer',
        'status' => 'boolean'
    ];

    public function mount()
    {
        $this->departments = Department::get();
    }

    public function render()
    {
        $courses = Course::with('department')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.course-manager', compact('courses'));
    }

    public function openModal()
    {
        $this->resetFields();
        $this->isOpen = true;
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);

        $this->courseId = $id;
        $this->name = $course->name;
        $this->dept_id = $course->dept_id;
        $this->program_code = $course->program_code;
        $this->types = $course->types;
        $this->status = $course->status;

        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate();

        Course::updateOrCreate(
            ['id' => $this->courseId],
            [
                'name' => $this->name,
                'dept_id' => $this->dept_id,
                'program_code' => $this->program_code,
                'types' => $this->types,
                'status' => $this->status
            ]
        );

        session()->flash('success',
            $this->courseId ? 'Course updated successfully.' : 'Course created successfully.'
        );

        $this->resetFields();
        $this->isOpen = false;
    }

    private function resetFields()
    {
        $this->courseId = null;
        $this->name = '';
        $this->dept_id = '';
        $this->program_code = '';
        $this->types = '';
        $this->status = 1;
    }
}