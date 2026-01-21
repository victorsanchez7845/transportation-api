<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenpayWeebhookAvailable extends Model
{
    use HasFactory;
    protected $table = 'openpay_weebhooks_available';
    protected $fillable = [
        'event_type',
        'verification_code',
    ];
}
