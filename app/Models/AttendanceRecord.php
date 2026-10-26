<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_sheet_id',
        'day_number',
        'day_date',
        'status',
        'notes',
        'is_signed',
    ];

    protected $casts = [
        'is_signed' => 'boolean',
    ];

    public function attendanceSheet(): BelongsTo
    {
        return $this->belongsTo(AttendanceSheet::class);
    }
}
