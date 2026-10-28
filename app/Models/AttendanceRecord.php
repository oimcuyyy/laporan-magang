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
        'paraf_path',
    ];

    protected $casts = [
        'is_signed' => 'boolean',
    ];

    public function getParafUrlAttribute(): ?string
    {
        if (! $this->paraf_path) {
            return null;
        }

        if (str_starts_with($this->paraf_path, 'data:image/') || str_starts_with($this->paraf_path, 'http')) {
            return $this->paraf_path;
        }

        return asset('storage/'.$this->paraf_path);
    }

    public function attendanceSheet(): BelongsTo
    {
        return $this->belongsTo(AttendanceSheet::class);
    }
}
