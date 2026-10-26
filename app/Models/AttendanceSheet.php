<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'month_name',
        'total_target_days',
        'total_present',
        'total_sick',
        'total_permission',
        'total_absent',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class)->orderBy('day_number');
    }

    /**
     * Hitung ulang rekapitulasi kehadiran berdasarkan records
     */
    public function recalculateTotals(): void
    {
        $records = $this->records;
        $present = $records->where('status', 'H')->count();
        $sick = $records->where('status', 'S')->count();
        $permission = $records->where('status', 'I')->count();
        $absent = $records->where('status', 'A')->count();

        $this->update([
            'total_target_days' => $records->count(),
            'total_present' => $present,
            'total_sick' => $sick,
            'total_permission' => $permission,
            'total_absent' => $absent,
        ]);
    }
}
