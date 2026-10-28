<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\Evaluation;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;

class EvaluationController extends Controller
{
    /**
     * Update Penilaian Magang DU/DI & Hitung Otomatis Predikat
     */
    public function update(UpdateEvaluationRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();

        $scoreA = (float) $validated['aspect_a_score'];
        $scoreB = (float) $validated['aspect_b_score'];
        $scoreC = (float) $validated['aspect_c_score'];

        $gradeA = Evaluation::calculateGrade($scoreA);
        $gradeB = Evaluation::calculateGrade($scoreB);
        $gradeC = Evaluation::calculateGrade($scoreC);

        $average = round(($scoreA + $scoreB + $scoreC) / 3, 2);
        $finalGrade = Evaluation::calculateGrade($average);

        $student->evaluation()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'aspect_a_score' => $scoreA,
                'aspect_a_grade' => $gradeA,
                'aspect_b_score' => $scoreB,
                'aspect_b_grade' => $gradeB,
                'aspect_c_score' => $scoreC,
                'aspect_c_grade' => $gradeC,
                'average_score' => $average,
                'final_grade' => $finalGrade,
                'evaluation_date' => $validated['evaluation_date'],
                'mentor_name' => $validated['mentor_name'] ?? $student->mentor_name,
            ]
        );

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'penilaian',
        ])->with('success', "Penilaian DU/DI berhasil disimpan! Rata-rata: {$average} (Predikat {$finalGrade})");
    }
}
