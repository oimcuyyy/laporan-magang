<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month_name' => 'required|string|max:100',
            'records' => 'required|array',
            'records.*.id' => 'required|integer|exists:attendance_records,id',
            'records.*.day_date' => 'nullable|string|max:100',
            'records.*.status' => 'nullable|in:H,S,I,A',
            'records.*.notes' => 'nullable|string|max:150',
            'records.*.is_signed' => 'nullable|boolean',
        ];
    }
}
