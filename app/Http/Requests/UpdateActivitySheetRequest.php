<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivitySheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sheet = $this->route('sheet');
        $student = $this->route('student');

        if ($sheet && $student && $sheet->student_id !== $student->id) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'month_name' => 'required|string|max:100',
            'logs' => 'required|array',
            'logs.*.id' => 'required|integer|exists:activity_logs,id',
            'logs.*.day_date' => 'nullable|string|max:100',
            'logs.*.division' => 'nullable|string|max:100',
            'logs.*.job_description' => 'nullable|string',
            'logs.*.supervisor_note' => 'nullable|string|max:100',
            'logs.*.is_approved' => 'nullable|boolean',
            'logs.*.paraf_data' => 'nullable|string',
            'logs.*.paraf_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logs.*.remove_paraf' => 'nullable|boolean',
            'logs.*.paraf_path' => 'nullable|string',
        ];
    }
}
