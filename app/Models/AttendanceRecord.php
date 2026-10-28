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
        if ($this->paraf_path === 'none') {
            return null;
        }

        $path = $this->paraf_path;

        if (! $path) {
            // Hanya warisi paraf otomatis jika hadir (H) atau ditandai is_signed
            if ($this->status !== 'H' && ! $this->is_signed) {
                return null;
            }

            $path = $this->attendanceSheet?->student?->default_paraf_path;
        }

        if (! $path || $path === 'none') {
            return null;
        }

        if (str_starts_with($path, 'data:image/') || str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function getIsUsingDefaultParafAttribute(): bool
    {
        return empty($this->paraf_path) && ! empty($this->attendanceSheet?->student?->default_paraf_path);
    }

    public function getHasCustomParafAttribute(): bool
    {
        return ! empty($this->paraf_path) && $this->paraf_path !== 'none';
    }

    public function attendanceSheet(): BelongsTo
    {
        return $this->belongsTo(AttendanceSheet::class);
    }
}
