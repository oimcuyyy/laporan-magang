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
        if (! $this->paraf_path) {
            return null;
        }

        if (str_starts_with($this->paraf_path, 'data:image/') || str_starts_with($this->paraf_path, 'http')) {
            return $this->paraf_path;
        }

        return asset('storage/'.$this->paraf_path);
    }

    public function activitySheet(): BelongsTo
    {
        return $this->belongsTo(ActivitySheet::class);
    }
}
