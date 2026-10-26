<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WafLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'method',
        'request_url',
        'threat_type',
        'severity',
        'matched_pattern',
        'blocked_payload',
        'user_agent',
    ];
}
