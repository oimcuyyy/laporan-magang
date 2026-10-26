<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'aspect_a_score' => is_string($this->aspect_a_score) ? str_replace(',', '.', $this->aspect_a_score) : $this->aspect_a_score,
            'aspect_b_score' => is_string($this->aspect_b_score) ? str_replace(',', '.', $this->aspect_b_score) : $this->aspect_b_score,
            'aspect_c_score' => is_string($this->aspect_c_score) ? str_replace(',', '.', $this->aspect_c_score) : $this->aspect_c_score,
        ]);
    }

    public function rules(): array
    {
        return [
            'aspect_a_score' => 'required|numeric|min:0|max:100',
            'aspect_b_score' => 'required|numeric|min:0|max:100',
            'aspect_c_score' => 'required|numeric|min:0|max:100',
            'evaluation_date' => 'required|string|max:100',
            'mentor_name' => 'nullable|string|max:100',
        ];
    }
}
