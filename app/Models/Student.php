<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nis',
        'nisn',
        'class',
        'major',
        'field_of_expertise',
        'internship_period',
        'academic_year',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'student_address',
        'student_phone',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'parent_address',
        'school_name',
        'school_address',
        'company_name',
        'company_address',
        'teacher_name',
        'teacher_phone',
        'mentor_name',
        'photo_path',
        'mentor_signature_path',
        'default_paraf_path',
    ];

    public function activitySheets(): HasMany
    {
        return $this->hasMany(ActivitySheet::class)->orderBy('sheet_number');
    }

    public function attendanceSheets(): HasMany
    {
        return $this->hasMany(AttendanceSheet::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/'.$this->photo_path);
        }

        return null;
    }

    public function getMentorSignatureUrlAttribute(): ?string
    {
        $path = $this->mentor_signature_path;

        // Fallback cerdas: jika tanda tangan mentor belum diupload tersendiri tapi master paraf sudah ada
        if (! $path) {
            $path = $this->default_paraf_path;
        }

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'data:image/') || str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function getDefaultParafUrlAttribute(): ?string
    {
        $path = $this->default_paraf_path;

        // Fallback cerdas: jika master paraf belum diupload tapi tanda tangan mentor sudah ada
        if (! $path) {
            $path = $this->mentor_signature_path;
        }

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'data:image/') || str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
