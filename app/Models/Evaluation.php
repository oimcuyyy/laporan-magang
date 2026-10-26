<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'aspect_a_score',
        'aspect_a_grade',
        'aspect_b_score',
        'aspect_b_grade',
        'aspect_c_score',
        'aspect_c_grade',
        'average_score',
        'final_grade',
        'evaluation_date',
        'mentor_name',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Konversi nilai angka ke predikat huruf sesuai acuan:
     * 91 - 100 : A (Sangat Baik)
     * 81 - 90  : B (Baik)
     * 75 - 80  : C (Cukup Baik)
     * < 75     : D (Kurang)
     */
    public static function calculateGrade(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        if ($score >= 91) {
            return 'A';
        } elseif ($score >= 81) {
            return 'B';
        } elseif ($score >= 75) {
            return 'C';
        } else {
            return 'D';
        }
    }
}
