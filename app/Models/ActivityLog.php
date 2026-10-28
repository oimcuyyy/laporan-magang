<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_sheet_id',
        'row_index',
        'day_date',
        'division',
        'job_description',
        'supervisor_note',
        'paraf_path',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function getParafUrlAttribute(): ?string
    {
        if ($this->paraf_path === 'none') {
            return null;
        }

        $path = $this->paraf_path;

        if (! $path) {
            // Hanya warisi paraf otomatis jika baris kegiatan memiliki konten
            if (! $this->day_date && ! $this->job_description && ! $this->division) {
                return null;
            }

            $path = $this->activitySheet?->student?->default_paraf_path;
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
        return empty($this->paraf_path) && ! empty($this->activitySheet?->student?->default_paraf_path);
    }

    public function getHasCustomParafAttribute(): bool
    {
        return ! empty($this->paraf_path) && $this->paraf_path !== 'none';
    }

    public function activitySheet(): BelongsTo
    {
        return $this->belongsTo(ActivitySheet::class);
    }
}
