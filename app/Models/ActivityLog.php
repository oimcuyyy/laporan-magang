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
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function activitySheet(): BelongsTo
    {
        return $this->belongsTo(ActivitySheet::class);
    }
}
