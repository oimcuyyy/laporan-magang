<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationApiController extends Controller
{
    /**
     * PUT /api/v1/students/{student}/evaluation
     * Update penilaian DU/DI & hitung otomatis predikat A/B/C/D
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'aspect_a_score' => 'required|numeric|min:0|max:100',
            'aspect_b_score' => 'required|numeric|min:0|max:100',
            'aspect_c_score' => 'required|numeric|min:0|max:100',
            'evaluation_date' => 'required|string|max:100',
            'mentor_name' => 'nullable|string|max:100',
        ]);

        $scoreA = (float) $validated['aspect_a_score'];
        $scoreB = (float) $validated['aspect_b_score'];
        $scoreC = (float) $validated['aspect_c_score'];

        $gradeA = Evaluation::calculateGrade($scoreA);
        $gradeB = Evaluation::calculateGrade($scoreB);
        $gradeC = Evaluation::calculateGrade($scoreC);

        $average = round(($scoreA + $scoreB + $scoreC) / 3, 2);
        $finalGrade = Evaluation::calculateGrade($average);

        $eval = $student->evaluation()->updateOrCreate(
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

        return response()->json([
            'success' => true,
            'message' => "Penilaian DU/DI berhasil disimpan! Nilai Rata-rata: {$average} ({$finalGrade})",
            'data' => $eval,
        ]);
    }
}
