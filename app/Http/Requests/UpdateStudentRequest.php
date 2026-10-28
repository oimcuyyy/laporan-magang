<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student') ? $this->route('student')->id : null;

        return [
            'name' => 'required|string|max:150',
            'nis' => 'required|string|max:30|unique:students,nis,'.$studentId,
            'nisn' => 'nullable|string|max:30',
            'class' => 'nullable|string|max:50',
            'major' => 'nullable|string|max:100',
            'field_of_expertise' => 'nullable|string|max:100',
            'internship_period' => 'nullable|string|max:100',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|string|max:50',
            'gender' => 'required|in:Laki-Laki,Perempuan',
            'religion' => 'nullable|string|max:30',
            'student_address' => 'nullable|string|max:500',
            'student_phone' => 'nullable|string|max:30',
            'father_name' => 'nullable|string|max:100',
            'father_phone' => 'nullable|string|max:30',
            'mother_name' => 'nullable|string|max:100',
            'mother_phone' => 'nullable|string|max:30',
            'parent_address' => 'nullable|string|max:500',
            'school_name' => 'required|string|max:150',
            'school_address' => 'nullable|string|max:500',
            'company_name' => 'required|string|max:150',
            'company_address' => 'nullable|string|max:500',
            'teacher_name' => 'required|string|max:100',
            'teacher_phone' => 'nullable|string|max:30',
            'mentor_name' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'mentor_signature' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'mentor_signature_data' => 'nullable|string',
            'remove_mentor_signature' => 'nullable|boolean',
        ];
    }
}
