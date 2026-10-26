<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivitySheetRequest extends FormRequest
{
    public function authorize(): bool
    {
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
        ];
    }
}
