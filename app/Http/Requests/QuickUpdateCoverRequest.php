<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickUpdateCoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'major' => 'required|string|max:100',
            'class' => 'required|string|max:50',
            'academic_year' => 'nullable|string|max:30',
            'field_of_expertise' => 'nullable|string|max:100',
        ];
    }
}
